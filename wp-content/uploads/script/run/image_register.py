import concurrent.futures
import subprocess
import threading
import re
import os
import argparse

parser = argparse.ArgumentParser(description='register image to wordpress')
parser.add_argument('--website-com', default="www.oliviamark.com", type=str)
parser.add_argument('--watermark-name', default='oliviamark', type=str)
parser.add_argument('--max-workers', default=1, type=int)
args = parser.parse_args()

website_name = args.watermark_name
website_com = args.website_com
script_dir = rf"/home/{website_name}/htdocs/{website_com}/wp-content/uploads/script/run"
max_workers = args.max_workers


def write_error_log(image_url, error):
    with open(rf"{script_dir}/error.log", "a", encoding="utf-8") as f:
        f.write(f"{image_url} error: {error}\n")


def write_import_result_into_file(output):
    with lock:
        try:
            # response
            with open(rf"{script_dir}/result.txt", "a",
                      encoding="utf-8") as f:
                f.write(f"{output}\n")
        except Exception as e:
            print("OUTPUT ERROR:", e)


def cmd_run_php(image_url, command):
    try:
        log = subprocess.check_output(command, shell=True, text=True)
        write_import_result_into_file(log)
        print(log)
    except Exception as e:
        print(f"{command} error: {e}")
        write_error_log(image_url, e)


if __name__ == '__main__':
    with open(rf"{script_dir}/media_import_metadata.txt", "r",
              encoding="utf-8") as f:
        image_info_list = f.readlines()

    image_data = {}
    if os.path.exists(rf"{script_dir}/result.txt"):
        with open(rf"{script_dir}/result.txt", "r",
                  encoding="utf-8") as f:
            result_list = f.readlines()
            for result in result_list:
                pattern = r"Imported file '(.+)' as attachment ID (\d+)\."
                matches = re.findall(pattern, result)
                for match in matches:
                     file_path, attachment_id = match
                     image_data[file_path] = attachment_id

    command_dict = {}

    lock = threading.RLock()

    count = 0
    for image_info in image_info_list:
        image_info = image_info.strip()
        size = image_info.split(",")[-1]
        height = image_info.split(",")[-2]
        width = image_info.split(",")[-3]
        image_url = image_info.replace(f",{width},{height},{size}", "")
        if image_url in image_data.keys():
            continue
        command = f'php /home/{website_name}/wp-cli.phar media import --external-import-skip-download-skip-dedup-path-as-post-name "{image_url}" --width={width} --height={height} --size={size}'
        command_dict[image_info.split(",")[0]] = command
        count += 1
    print("total count = ", count)

    with concurrent.futures.ThreadPoolExecutor(max_workers=max_workers) as executor:
        executor.map(cmd_run_php, command_dict.keys(), command_dict.values())
