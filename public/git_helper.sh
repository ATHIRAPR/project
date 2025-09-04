#!/bin/bash
 
# Clone a repository with PAT
clone_repo() {
    local repo_url="$1"
    local local_path="$2"
    local pat="$3"
 
    if [ ! -d "$local_path/.git" ]; then
        local parent_dir
        parent_dir=$(dirname "$local_path")
 
        if [ ! -d "$parent_dir" ]; then
            mkdir -p "$parent_dir" || {
                echo "Failed to create parent directory: $parent_dir"
                return 1
            }
        fi
 
        # Inject PAT into the URL
        local auth_url="${repo_url/https:\/\//https:\/\/$pat@}"
        echo "Cloning from $auth_url..."
        git clone "$auth_url" "$local_path" 2>&1
        if [ $? -ne 0 ]; then
            echo "Git clone failed"
            return 1
        fi
 
        echo "Repository cloned into $local_path"
    else
        echo "Repository already exists at $local_path"
    fi
}
 
# Pull latest changes
pull_repo() {
    local local_path="$1"
 
    if [ ! -d "$local_path/.git" ]; then
        echo "Not a Git repo: $local_path"
        return 1
    fi
 
    echo "Pulling latest changes in $local_path"
    (cd "$local_path" && git pull 2>&1)
    if [ $? -ne 0 ]; then
        echo "Git pull failed"
        return 1
    fi
 
    echo "Repository updated: $local_path"
}
 
# Entry point: allows calling functions like:
# ./git_helper.sh clone_repo <repo_url> <local_path> <pat>
# ./git_helper.sh pull_repo <local_path>
if [[ "$1" == "clone_repo" ]]; then
    shift
    clone_repo "$@"
elif [[ "$1" == "pull_repo" ]]; then
    shift
    pull_repo "$@"
else
    echo "Usage:"
    echo "  $0 clone_repo <repo_url> <local_path> <pat>"
    echo "  $0 pull_repo <local_path>"
    exit 1
fi
