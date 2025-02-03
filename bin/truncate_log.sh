# Get today's date in the log file format (YYYY-MM-DD)
today=$(date +"%Y-%m-%d")
log_dir="${INSTALL_DIR}application/logs/"

# Construct the expected log file name
log_file="log-$today.php"
full_log_path="${log_dir}${log_file}"

# Check if the log file exists
if [ -f "$full_log_path" ]; then
    echo "Today's log file found: $full_log_path"
    truncate -s 0 $full_log_path
else
    echo "No log file found for today ($today)."
fi