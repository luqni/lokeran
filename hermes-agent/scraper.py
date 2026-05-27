import re
import json
import logging
from bs4 import BeautifulSoup
import httpx
from playwright.sync_api import sync_playwright
from config import settings

logging.basicConfig(level=logging.INFO, format="%(asctime)s [%(levelname)s] %(message)s")
logger = logging.getLogger("hermes.scraper")

# Platform Configurations (Search URLs and URL extraction regexes)
PLATFORM_CONFIGS = {
    'LinkedIn': {
        'search_url': "https://www.linkedin.com/jobs/search?location=Indonesia&f_TPR=r604800",
        'regex': r'(https://[a-z]{2}\.linkedin\.com/jobs/view/[^\)\s"\'<>]+)',
        'use_browser': True
    },
    'JobStreet': {
        'search_url': "https://id.jobstreet.com/id/jobs?daterange=7",
        'regex': r'(https://(?:id|www)\.jobstreet\.(?:com|co\.id)/id/job/[^\)\s"\'<>?]+)',
        'use_browser': True
    },
    'Indeed': {
        'search_url': "https://id.indeed.com/jobs?q=dibutuhkan+segera&l=Indonesia",
        'regex': r'(https://id\.indeed\.com/(?:rc/clk|viewjob)\?[^\)\s"\'<>]+)',
        'use_browser': True
    },
    'Karir.com': {
        'search_url': "https://www.karir.com/search",
        'regex': r'(https://www\.karir\.com/opportunities/[0-9]+)',
        'use_browser': False
    },
    'Loker.id': {
        'search_url': "https://www.loker.id/cari-lowongan-kerja",
        'regex': r'(https://www\.loker\.id/(?:lowongan/[^\)\s"\'<>]+|[^\)\s"\'<>]+/[0-9]+-[^\)\s"\'<>]+\.html))',
        'use_browser': False
    },
    'Karirhub Kemnaker': {
        'search_url': "https://karirhub.kemnaker.go.id/vacancies",
        'regex': r'(https://karirhub\.kemnaker\.go\.id/(?:vacancies|lowongan)/[a-f0-9\-]+)',
        'use_browser': False
    }
}

class HermesScraper:
    def __init__(self):
        self.client = httpx.Client(
            headers={
                "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
            },
            timeout=30.0
        )

    def fetch_page_content(self, url: str, use_browser: bool = False) -> str:
        """Fetches the content of a page using HTTPX or Playwright browser automation."""
        if not use_browser:
            try:
                response = self.client.get(url)
                if response.status_code == 200:
                    return response.text
                logger.warning(f"Standard fetch failed for {url}. Status code: {response.status_code}")
            except Exception as e:
                logger.error(f"Error in standard fetch for {url}: {str(e)}")

        # Fallback/Direct Browser Automation for Protected Sites (LinkedIn, JobStreet, Indeed)
        logger.info(f"Using browser automation (Playwright) to load: {url}")
        try:
            with sync_playwright() as p:
                browser = p.chromium.launch(headless=True)
                # Set dynamic user agent and viewport to look like human
                context = browser.new_context(
                    user_agent="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
                    viewport={"width": 1280, "height": 800}
                )
                page = context.new_page()
                page.goto(url, wait_until="networkidle", timeout=45000)
                
                # Sleep briefly to allow dynamic content render
                page.wait_for_timeout(3000)
                
                # Scroll down slightly to trigger lazy-loaded images/scripts
                page.evaluate("window.scrollBy(0, 500)")
                page.wait_for_timeout(1000)
                
                content = page.content()
                browser.close()
                return content
        except Exception as e:
            logger.error(f"Playwright automation failed for {url}: {str(e)}")
            return ""

    def extract_job_urls(self, platform_name: str, html: str) -> list:
        """Extracts detail URLs from a search page using regex matching."""
        config = PLATFORM_CONFIGS.get(platform_name)
        if not config:
            return []

        matches = re.findall(config['regex'], html)
        # Unique list
        unique_urls = list(set(matches))
        return unique_urls

    def extract_job_details_with_ai(self, raw_text: str) -> dict:
        """Leverages Gemini API via OpenAI-compatible endpoints to parse unstructured text into highly clean JSON data."""
        if not settings.GEMINI_API_KEY:
            logger.warning("No GEMINI_API_KEY set. Falling back to dummy mock data.")
            return self._mock_fallback(raw_text, "Gemini Key Missing")

        url = "https://generativelanguage.googleapis.com/v1beta/openai/chat/completions"
        headers = {
            "Authorization": f"Bearer {settings.GEMINI_API_KEY}",
            "Content-Type": "application/json"
        }

        prompt = (
            "Extract the following job posting into a valid JSON object with EXACTLY these keys: "
            "\"job_title\" (string), \"company_name\" (string, use null if not found), "
            "\"company_logo\" (string URL, search the text for any image URLs that represent the company logo, usually ending in .jpg, .png, or starting with domain-specific CDN paths, use null if not found), "
            "\"requirements\" (array of strings, extract key skills and qualifications as bullet points), "
            "\"location\" (string, extract city or country, default to Indonesia if not specified). "
            "DO NOT return any markdown formatting, only pure JSON string."
        )

        payload = {
            "model": "gemini-3.1-flash-lite",
            "response_format": {"type": "json_object"},
            "messages": [
                {"role": "system", "content": "You are a JSON job data extractor. You must only output a valid JSON object."},
                {"role": "user", "content": f"{prompt}\n\nRaw Text:\n{raw_text[:4000]}"}
            ]
        }

        try:
            response = self.client.post(url, json=payload, headers=headers, timeout=20.0)
            if response.status_code == 200:
                data = response.json()
                content = data['choices'][0]['message']['content']
                # Strip markdown code blocks just in case
                content = re.sub(r'```json\s*', '', content)
                content = re.sub(r'```\s*', '', content)
                return json.loads(content)
            else:
                logger.error(f"Gemini API returned status {response.status_code}: {response.text}")
                return self._mock_fallback(raw_text, f"Gemini HTTP Status {response.status_code}")
        except Exception as e:
            logger.error(f"Exception during Gemini AI parsing: {str(e)}")
            return self._mock_fallback(raw_text, str(e))

    def _mock_fallback(self, raw_text: str, reason: str) -> dict:
        """Provides safe fallback structured data if Gemini extraction fails."""
        lines = [line.strip() for line in raw_text.split("\n") if line.strip()]
        title = lines[0] if lines else "Unknown Job Title"
        if len(title) > 80:
            title = title[:80] + "..."
            
        return {
            "job_title": f"{title} (AI Raw)",
            "company_name": "Under Verification",
            "company_logo": None,
            "requirements": [
                "Peringatan: Ekstraksi AI mengalami kendala.",
                f"Alasan: {reason}",
                "Silakan cek deskripsi di URL asal."
            ],
            "location": "Indonesia"
        }

    def push_to_laravel(self, platform_name: str, job_data: dict, source_url: str) -> bool:
        """Sends extracted structured job details directly to the Laravel webhook API."""
        webhook_url = f"{settings.LARAVEL_API_URL}/webhooks/jobs"
        headers = {
            "X-Hermes-Token": settings.HERMES_WEBHOOK_TOKEN,
            "Content-Type": "application/json"
        }

        payload = {
            "platform_name": platform_name,
            "job_title": job_data.get("job_title", "Unknown Job"),
            "company_name": job_data.get("company_name"),
            "company_logo": job_data.get("company_logo"),
            "requirements": job_data.get("requirements", []),
            "source_url": source_url,
            "location": job_data.get("location", "Indonesia")
        }

        try:
            response = self.client.post(webhook_url, json=payload, headers=headers)
            if response.status_code in [200, 201]:
                logger.info(f"Successfully pushed job to Laravel: {payload['job_title']} ({payload['source_url']}) -> Status {response.status_code}")
                return True
            else:
                logger.error(f"Failed to push job to Laravel. Status: {response.status_code}, Body: {response.text}")
                return False
        except Exception as e:
            logger.error(f"Error pushing job to Laravel: {str(e)}")
            return False

    def scrape_platform(self, platform_name: str, max_jobs: int = 5) -> int:
        """Runs the complete scraping cycle for a specific platform."""
        config = PLATFORM_CONFIGS.get(platform_name)
        if not config:
            logger.error(f"Invalid platform specified: {platform_name}")
            return 0

        logger.info(f"=== Starting scrape cycle for platform: {platform_name} ===")
        
        # 1. Fetch Search Page
        html = self.fetch_page_content(config['search_url'], use_browser=config['use_browser'])
        if not html:
            logger.error(f"Failed to fetch search page content for {platform_name}")
            return 0

        # 2. Extract job URLs
        job_urls = self.extract_job_urls(platform_name, html)
        logger.info(f"Found {len(job_urls)} potential job URLs on search page.")
        
        # Limit the number of jobs per run to prevent API quota overload
        target_urls = job_urls[:max_jobs]
        successful_pushes = 0

        # 3. Process each job URL
        for index, url in enumerate(target_urls):
            logger.info(f"Processing job [{index+1}/{len(target_urls)}]: {url}")
            
            # Fetch details page content
            detail_html = self.fetch_page_content(url, use_browser=config['use_browser'])
            if not detail_html:
                logger.warning(f"Skipping job. Failed to fetch detail page content for: {url}")
                continue

            # Convert to plain text for LLM parsing
            soup = BeautifulSoup(detail_html, 'html.parser')
            # Remove scripts and styles
            for script in soup(["script", "style", "nav", "header", "footer"]):
                script.decompose()
            
            plain_text = soup.get_text(separator="\n")
            # Clean up whitespace
            cleaned_text = "\n".join([line.strip() for line in plain_text.split("\n") if line.strip()])
            
            # 4. Extract structured details with Gemini AI
            job_details = self.extract_job_details_with_ai(cleaned_text)
            
            # 5. Push to Laravel
            pushed = self.push_to_laravel(platform_name, job_details, url)
            if pushed:
                successful_pushes += 1

        logger.info(f"=== Completed scrape cycle for {platform_name}. Successfully pushed {successful_pushes} jobs. ===")
        return successful_pushes
