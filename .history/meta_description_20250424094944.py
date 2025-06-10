import requests
from bs4 import BeautifulSoup

def get_meta_description(url):
    try:
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        }
        response = requests.get(url, headers=headers, timeout=10)
        response.raise_for_status()

        soup = BeautifulSoup(response.text, 'html.parser')

        # Check for standard meta description
        meta_description = soup.find('meta', attrs={'name': 'description'})
        if meta_description:
            return meta_description.get('content', '').strip()

        # Check for Open Graph description if standard not found
        og_description = soup.find('meta', attrs={'property': 'og:description'})
        if og_description:
            return og_description.get('content', '').strip()

        return "No meta description found"

    except requests.exceptions.RequestException as e:
        return f"Error fetching URL: {str(e)}"
    except Exception as e:
        return f"Error parsing page: {str(e)}"

def clean_url(url):
    """Clean URL by fixing double slashes while preserving the protocol"""
    if not url:
        return url

    if ':///' in url:
        return url.replace(':///', '://')

    parts = url.split('://')
    if len(parts) == 2:
        return parts[0] + '://' + parts[1].replace('//', '/')
    return url

def process_urls(file_path):
    with open(file_path, 'r') as file:
        urls = [line.strip() for line in file if line.strip()]

    for original_url in urls:
        processed_url = clean_url(original_url)
        # print(f"\nURL: {processed_url}")
        description = get_meta_description(processed_url)
        print(description)

if __name__ == "__main__":
    input_file = "data/kt_en_nids.list"
    process_urls(input_file)
