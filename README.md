# OVH Swift Object Storage Ordering Bug Demonstration

## Background

I was using [Swift Large Objects](https://docs.openstack.org/swift/latest/overview_large_objects.html) to store ZIP files of server backups, and on testing I found that I couldn't unzip the files. After some exploration I found that if a large object is broken into more than 10 segments it is not reassembled in the correct order. This code demonstrates that with easily-readable, short, text files.

## What it does

Running main.php creates a file that's 12 lines long, with 1024 characters on each line, including the newline. Each line starts with the line number. That file is uploaded to a Swift instance as a "large" object with a segmetn size of 1024 bytes so each line is a separate segment, then downloaded, and the original and downloaded files are compared and displayed on screen.

## Installation & Configuration

### Installation

1. Clone the repository.
2. Run `composer install` in the repository.

### Configuration

Copy the .env.example file to .env and add values for the constants. 

## How to Use

Run the php file from the terminal: `php main.php`

## Notes

This does not delete the uploaded file between or after runs, so you'll have to do that manually. 