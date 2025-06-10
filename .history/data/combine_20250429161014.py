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
    missing_nids = []

    for meta_data in all_meta_data:
        nid = meta_data.get('nid')
        if not nid:
            print(f"Entry missing nid: {meta_data}")
            continue

        metatag_data = metatag_dict.get(nid)
        if metatag_data:
            csv_data = create_csv_output(meta_data, metatag_data)
            csv_data_list.append(csv_data)
        else:
            missing_nids.append(nid)

    # Write all data to CSV
    write_to_csv(csv_data_list, csv_output_path)
    print(f"CSV file created successfully at {csv_output_path} with {len(csv_data_list)} entries")

    # Report missing entries
    if missing_nids:
        print(f"\nWarning: Could not find metatag data for {len(missing_nids)} nids:")
        print(", ".join(missing_nids))

if __name__ == '__main__':
    main()
