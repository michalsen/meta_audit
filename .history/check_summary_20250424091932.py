import json
import requests
from bs4 import BeautifulSoup
from urllib.parse import urljoin

def fetch_meta_description(url):
    try:
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        }
        response = requests.get(url, headers=headers, timeout=10)
        response.raise_for_status()

        soup = BeautifulSoup(response.text, 'html.parser')

        # Check for meta description tag
        meta_description = soup.find('meta', attrs={'name': 'description'}) or \
                          soup.find('meta', attrs={'property': 'og:description'})

        if meta_description:
            return meta_description.get('content', '').strip()
        return None
    except Exception as e:
        print(f"Error fetching {url}: {str(e)}")
        return None

def compare_descriptions(json_file):
    with open(json_file, 'r', encoding='utf-8') as f:
        try:
            data = json.load(f)
        except json.JSONDecodeError:
            # If the file contains multiple JSON objects (not in an array)
            f.seek(0)
            data = []
            for line in f:
                if line.strip():
                    data.append(json.loads(line.strip().rstrip(',')))

    for item in data:
        nid = item.get('nid')
        view_node = item.get('view_node')
        field_summary = item.get('field_search_summary_text', '').strip()

        if not view_node:
            continue

        # Clean URL (remove double slashes)
        view_node = urljoin(view_node, view_node.replace('//', '/'))

        page_description = fetch_meta_description(view_node)

        if page_description is None:
            print(f"{nid}: Could not retrieve meta description from page")
            continue

        if page_description != field_summary:
            print(f"\nNID: {nid}")
            print(f"URL: {view_node}")
            print("Difference found:")
            print(f"JSON summary: {field_summary}")
            print(f"Page meta description: {page_description}\n")

if __name__ == "__main__":
    json_file = "data/meta_kt_en.json"
    compare_descriptions(json_file)
