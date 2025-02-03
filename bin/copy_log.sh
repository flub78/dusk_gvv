# Get today's date in the log file format (YYYY-MM-DD)
today=$(date +"%Y-%m-%d")
log_dir="${INSTALL_DIR}application/logs/"

# Construct the expected log file name
log_file="log-$today.php"
full_log_path="${log_dir}${log_file}"

if [ -f "$full_log_path" ]; then
    echo "Copy log file: $full_log_path"
    cp $full_log_path tests/reports/gvv_under_test.log
fi