#!/usr/bin/env python3

import re
import sys
from collections import Counter

LANG_PATTERN = r'Could not find the language line "(.*)"'
NOTICE_PATTERN = r'Severity: Notice  --> (.*)'
WARNING_PATTERN = r'Severity: Warning  --> (.*)'
ERROR_PATTERN = r'error(.*)'
ARROW_PATTERN = r' --> (.*)'

def process_logs(file_path):
    error_counts = Counter()
    notice_counts = Counter()
    warning_counts = Counter()
    error_msg_counts = Counter()

    # Read and process log file
    try:
        with open(file_path, "r", encoding="utf-8") as file:
            for line in file:
                if match := re.search(LANG_PATTERN, line):
                    error_message = match.group(1)
                    error_counts[error_message] += 1
                elif match := re.search(NOTICE_PATTERN, line):
                    notice = match.group(1)
                    notice_counts[notice] += 1
                elif match := re.search(WARNING_PATTERN, line):
                    warning = match.group(1)
                    warning_counts[warning] += 1
                elif match := re.search(ERROR_PATTERN, line):
                    if (not re.search(r'errors=0', line)):
                        match = re.search(ARROW_PATTERN, line)
                        error = match.group(1)
                        error_msg_counts[error] += 1

        # Print sorted results by frequency
        total_errors = sum(error_counts.values())
        print(f"\n--- Missing translation strings ({total_errors} total) ---\n")
        for error, count in error_counts.most_common():
            print(f"{count} | {error}")

        total_notices = sum(notice_counts.values())
        print(f"\n--- Notices ({total_notices} total) ---\n")
        for notice, count in notice_counts.most_common():
            print(f"{count} | {notice}")

        total_warnings = sum(warning_counts.values())
        print(f"\n--- Warnings ({total_warnings} total) ---\n")
        for warning, count in warning_counts.most_common():
            print(f"{count} | {warning}")

        total_error_msgs = sum(error_msg_counts.values())
        print(f"\n--- Errors ({total_error_msgs} total) ---\n")
        for error, count in error_msg_counts.most_common():
            print(f"{count} | {error}")

        # Generate CSV with error counts
        csv_filename = file_path.rsplit('.', 1)[0] + '.csv'
        with open(csv_filename, "w", encoding="utf-8") as csv_file:
            # csv_file.write("Pattern,Count\n")
            # csv_file.write(f"LANG_PATTERN,{sum(error_counts.values())}\n")
            # csv_file.write(f"NOTICE_PATTERN,{sum(notice_counts.values())}\n")
            # csv_file.write(f"WARNING_PATTERN,{sum(warning_counts.values())}\n")
            # csv_file.write(f"ERROR_PATTERN,{sum(error_msg_counts.values())}\n")
            csv_file.write("Lang,Notices,Warnings,Errors\n")
            csv_file.write(f"{sum(error_counts.values())},{sum(notice_counts.values())},{sum(warning_counts.values())},{sum(error_msg_counts.values())}\n")
        print(f"\nError counts have been written to {csv_filename}")

    except FileNotFoundError:
        print(f"Error: File '{file_path}' not found.")
    except Exception as e:
        print(f"An error occurred: {e}")

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python script.py <log_filename>")
    else:
        process_logs(sys.argv[1])

