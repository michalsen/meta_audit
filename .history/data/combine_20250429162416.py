import json
import csv

def read_meta_json(file_path):
    with open(file_path, 'r') as file:
        return json.load(file)

def read_metatag_export(metatag_file_path):
    metatag_data = {}
    with open(metatag_file_path, 'r') as file:
        for line in file:
            try:
                line_nid, json_str = line.strip().split('\t', 1)
                metatag_data[line_nid] = json.loads(json_str)
            except (ValueError, json.JSONDecodeError) as e:
                print(f"Error processing line: {line.strip()}. Error: {e}")
    return metatag_data

def create_csv_output(meta_data, metatag_data=None):
    output = {
        'nid': meta_data.get('nid', ''),
        'lang': meta_data.get('lang', ''),
        'title': meta_data.get('title', ''),
        'type': meta_data.get('type', ''),
        'view_node': meta_data.get('view_node', ''),
        'field_search_summary_text': meta_data.get('field_search_summary_text', '').replace('\n', ' ').strip(),
        # Metatag fields (will be empty if no match)
        'description': metatag_data.get('description', '').replace('\r\n', ' ').strip() if metatag_data else '',
        'og_description': metatag_data.get('og_description', '') if metatag_data else '',
        'og_image': metatag_data.get('og_image', '') if metatag_data else '',
        'og_image_secure_url': metatag_data.get('og_image_secure_url', '') if metatag_data else '',
        'og_image_url': metatag_data.get('og_image_url', '') if metatag_data else '',
        'twitter_cards_description': metatag_data.get('twitter_cards_description', '') if metatag_data else '',
        'twitter_cards_image': metatag_data.get('twitter_cards_image', '') if metatag_data else '',
        'twitter_cards_type': metatag_data.get('twitter_cards_type', '') if metatag_data else ''
    }
    return output

def write_to_csv(data_list, output_file):
    if not data_list:
        print("No data to write to CSV")
        return

    fieldnames = data_list[0].keys()
    with open(output_file, 'w', newline='', encoding='utf-8') as csvfile:
        writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
        writer.writeheader()
        writer.writerows(data_list)

def main():
    # Input files
    meta_json_path = 'meta.json'
    metatag_export_path = 'metatagexport.json'

    # Output file
    csv_output_path = 'output.csv'

    # Read all meta data
    all_meta_data = read_meta_json(meta_json_path)
    if not isinstance(all_meta_data, list):
        all_meta_data = [all_meta_data]

    # Read all metatag data into a dictionary for quick lookup
    metatag_dict = read_metatag_export(metatag_export_path)

    # Process all meta entries
    csv_data_list = []
    matched_nids = []

    for meta_data in all_meta_data:
        nid = meta_data.get('nid')
        if not nid:
            print(f"Entry missing nid: {meta_data}")
            continue

        metatag_data = metatag_dict.get(nid)
        csv_data = create_csv_output(meta_data, metatag_data)
        csv_data_list.append(csv_data)

        if metatag_data:
            matched_nids.append(nid)

    # Write all data to CSV
    write_to_csv(csv_data_list, csv_output_path)
    print(f"CSV file created successfully at {csv_output_path} with {len(csv_data_list)} entries")

    # Report matching statistics
    total_entries = len(all_meta_data)
    matched_entries = len(matched_nids)
    print(f"\nMatching statistics:")
    print(f"- Total entries processed: {total_entries}")
    print(f"- Entries with metatag matches: {matched_entries} ({matched_entries/total_entries:.1%})")
    print(f"- Entries without metatag matches: {total_entries - matched_entries}")

if __name__ == '__main__':
    main()
