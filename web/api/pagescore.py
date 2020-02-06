from urllib.request import urlparse, urljoin
from bs4 import BeautifulSoup
from collections import deque
import requests
import requests.exceptions
import subprocess
import argparse
import signal
import logging
import re
import sys
import os

class TimeoutException(Exception):
    pass

def timeout_handler(signum, frame):
    raise TimeoutException

signal.signal(signal.SIGALRM, timeout_handler)

formatter = logging.Formatter("%(asctime)s-%(levelname)s-%(process)s-%(message)s")
# url for testing: "https://scrapethissite.com/"

# set default score
score = 0.0

# initialize the set of links (unique links)
internal_urls = set()
external_urls = set()

total_urls_visited = 0

def is_valid(url):
    """
    Checks whether `url` is a valid URL.
    """
    parsed = urlparse(url)
    return bool(parsed.netloc) and bool(parsed.scheme)


def get_all_website_links(url):
    """
    Returns all URLs that is found on `url` in which it belongs to the same website
    """
    # all URLs of `url`
    urls = set()
    # domain name of the URL without the protocol
    domain_name = urlparse(url).netloc
    soup = BeautifulSoup(requests.get(url).content, "html.parser")
    for a_tag in soup.findAll("a"):
        href = a_tag.attrs.get("href")
        if href == "" or href is None:
            # href empty tag
            continue
        # join the URL if it's relative (not absolute link)
        href = urljoin(url, href)
        parsed_href = urlparse(href)
        # remove URL GET parameters, URL fragments, etc.
        href = parsed_href.scheme + "://" + parsed_href.netloc + parsed_href.path
        if not is_valid(href):
            # not a valid URL
            continue
        if href in internal_urls:
            # already in the set
            continue
        if domain_name not in href:
            # external link
            if href not in external_urls:
                # print(f"{GRAY}[!] External link: {href}{RESET}")
                external_urls.add(href)
            continue
        # print(f"{GREEN}[*] Internal link: {href}{RESET}")
        urls.add(href)
        internal_urls.add(href)
    return urls


def crawl(url, max_urls):
    """
    Crawls a web page and extracts all links.
    You'll find all links in `external_urls` and `internal_urls` global set variables.
    params:
        max_urls (int): number of max urls to crawl, default is 10.
    """
    global total_urls_visited
    total_urls_visited += 1
    links = get_all_website_links(url)
    for link in links:
        if total_urls_visited > max_urls:
            break
        crawl(link, max_urls=max_urls)
    if len(external_urls) is 0:
        score = 0.0
    else:
        rank = len(internal_urls) / len(external_urls)
        score = rank / max_urls
        return score

def main(argv):
    title = "Data Dogs Analytics - Senior Project Page Score Ranker"
    parser = argparse.ArgumentParser(description=title)
    # passing flags
    parser.add_argument("--domain", "-d", required=True, help="domain name of the website.")
    parser.parse_args()

    # read arguments from the command line
    args = parser.parse_args()
    domain = args.domain
    for i in range(1):
        signal.alarm(10)
        try:
            # start page rank process
            score = crawl(domain, max_urls=1)
            print("Score:", round(score, 2))
        except TimeoutException as e:
            formatter = logging.Formatter("%(asctime)s-%(levelname)s-%(process)s-%(message)s")
            # error logger
            error_logger = setup_logger("error_logger", "./logs/datadogs_pagescore_error_logfile.log")
            error_logger.info("Error occurred")

            # exception error logger
            exception_logger = setup_logger("exception_logger", "./logs/datadogs_pagescore_exception_logfile.log")
            exception_logger.exception("Timeout exception occurred")

            continue
        else:
            signal.alarm(0)

def setup_logger(name, log_file, level=logging.INFO):
    handler = logging.FileHandler(log_file)
    handler.setFormatter(formatter)

    logger = logging.getLogger(name)
    logger.setLevel(level)
    logger.addHandler(handler)

    return logger

if __name__ == "__main__":
    main(sys.argv[1:])