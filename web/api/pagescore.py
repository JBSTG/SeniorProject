from bs4 import BeautifulSoup
from urllib.parse import urlsplit
from urllib.parse import urlparse
from collections import deque
import requests
import requests.exceptions
import subprocess
import argparse
import re
import sys
import os

# url for testing: "https://scrapethissite.com/"
# set of urls that have been processed
processed_urls = set()

# set of domains inside the target webiste
local_urls = set()

# set of domains outside of the target website
foreign_urls = set()

# set of broken urls
broken_urls = set()

# set the base case score default
t = 1

# set variable for number of urls
nURLs = 0

def grab_url(domain):
    # queue of urls to be crawled next
    new_urls = deque([domain])

    # use global nURLs to midify global variable counter
    global nURLs

    try:
        # process urls one by one until queue is exhausted
        while len(new_urls):
            # move url from queue to processed url set
            url = new_urls.popleft()
            processed_urls.add(url)

            # print the current url
            # print("Processing %s" % url)
            try:
                response = requests.get(url)
            except(requests.exceptions.MissingSchema, requests.exceptions.ConnectionError,
                    requests.exceptions.InvalidURL, requests.exceptions.InvalidSchema):
                # add broken urls to its own set
                broken_urls.add(url)
                continue
            # extract base url to resolve relative links
            parts = urlsplit(url)
            base = "{0.netloc}".format(parts)
            strip_base = base.replace("www.", "")
            base_url = "{0.scheme}://{0.netloc}".format(parts)
            path = url[:url.rfind('/')+1] if "/" in parts.path else url

            # using beautiful soup for html document
            soup = BeautifulSoup(response.text, "lxml")

            for link in soup.find_all("a"):
                # extracting link url from tag
                anchor = link.attrs["href"] if "href" in link.attrs else ""
                if anchor.startswith("/"):
                    local_link = base_url + anchor
                    local_urls.add(local_link)
                elif strip_base in anchor:
                    local_urls.add(anchor)
                elif not anchor.startswith("http"):
                    local_link = path + anchor
                    local_urls.add(local_link)
                else:
                    foreign_urls.add(anchor)
                new_urls.append(link)
                nURLs = len(new_urls)
            set_rank(nURLs, t)
    except KeyboardInterrupt:
        print(" Keyboard Interrupt\n")
        sys.exit()

def set_rank(url, t):
    if url is 0:
        score = -1.0
    else:
        sum = (1 - (t - 1)) / url
        score = (((1 - sum) * t) - sum) / 2
    if score < 0:
        score = score * -1
    print("Score:", round(score, 2))

def main(argv):
    title = "Data Dogs - Senior Project Page Score Ranker"
    parser = argparse.ArgumentParser(description=title)
    # passing flags
    parser.add_argument("--domain", "-d", required=True,
            help="domain name of the website you want to give a page score.")
    parser.parse_args()

    # read arguments from the command line
    args = parser.parse_args()
    domain = args.domain
    grab_url(domain)

if __name__ == "__main__":
    main(sys.argv[1:])
