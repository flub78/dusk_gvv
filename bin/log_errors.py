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
                    # print(f"Lang: {error_message}")
                elif match := re.search(NOTICE_PATTERN, line):
                    notice = match.group(1)
                    notice_counts[notice] += 1
                elif match := re.search(WARNING_PATTERN, line):
                    warning = match.group(1)
                    warning_counts[warning] += 1
                elif match := re.search(ERROR_PATTERN, line):
                    match = re.search(ARROW_PATTERN, line)
                    error = match.group(1)
                    error_msg_counts[error] += 1
                    # print(f"Error: {error}")

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

    except FileNotFoundError:
        print(f"Error: File '{file_path}' not found.")
    except Exception as e:
        print(f"An error occurred: {e}")

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python script.py <log_filename>")
    else:
        process_logs(sys.argv[1])

