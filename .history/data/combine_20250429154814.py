import json
import csv

def read_meta_json(file_path):
    with open(file_path, 'r') as file:
        return json.load(file)

def find_metatag_row(nid, metatag_file_path):
    with open(metatag_file_path, 'r') as file:
        for line in file:
            line_nid, json_str = line.strip().split('\t', 1)
            if line_nid == nid:
                return json.loads(json_str)
    return None

def create_csv_output(meta_data, metatag_data):
    output = {
        'nid': meta_data.get('nid', ''),
        'lang': meta_data.get('lang', ''),
        'title': meta_data.get('title', ''),
        'type': meta_data.get('type', ''),
        'view_node': meta_data.get('view_node', ''),
        'field_search_summary_text': meta_data.get('field_search_summary_text', '').replace('\n', ' ').strip(),
        'description': metatag_data.get('description', '').replace('\r\n', ' ').strip(),
        'og_description': metatag_data.get('og_description', ''),
        'og_image': metatag_data.get('og_image', ''),
        'og_image_secure_url': metatag_data.get('og_image_secure_url', ''),
        'og_image_url': metatag_data.get('og_image_url', ''),
        'twitter_cards_description': metatag_data.get('twitter_cards_description', ''),
        'twitter_cards_image': metatag_data.get('twitter_cards_image', ''),
        'twitter_cards_type': metatag_data.get('twitter_cards_type', '')
    }
    return output

def write_to_csv(data, output_file):
    if not data:
        return

    fieldnames = data.keys()
    with open(output_file, 'w', newline='', encoding='utf-8') as csvfile:
        writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
        writer.writeheader()
        writer.writerow(data)

def main():
    # Input files
    meta_json_path = 'meta.json'
    metatag_export_path = 'metatagexport.json'

    # Output file
    csv_output_path = 'output.csv'

    # Read meta.json
    meta_data = read_meta_json(meta_json_path)

    # Find corresponding row in metatagexport.json
    nid = meta_data['nid']
    metatag_data = find_metatag_row(nid, metatag_export_path)

    if metatag_data:
        # Create combined output
        csv_data = create_csv_output(meta_data, metatag_data)

        # Write to CSV
        write_to_csv(csv_data, csv_output_path)
        print(f"CSV file created successfully at {csv_output_path}")
    else:
        print(f"No matching row found in metatagexport.json for nid: {nid}")

if __name__ == '__main__':
    main()
