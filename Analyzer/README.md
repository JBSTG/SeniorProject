Web Crawling and Community Review to Prevent Misleading Links Analyzer

Getting Started

    These instructions will get you a copy of the project up and running on your local machine
    for development and testing purposes. See deployment for notes on how to deploy the project
    on a live system.

Prerequisites

    Python3
    python-pip3
    python-lxml
    clickbait_data.csv

Installing

    In order to install the packages, be sure that you are using a Unix environment. If you are
    use the folloing command to install them Linux/Unix use the following:

        sudo apt-get install {package name}

Running the tests

 - Explain how to run the automated tests for this system
    Break down into end to end tests

    Once you have the necessary packages and python3 installed you will now be able to run the script
    on your environment. To run the url script simply run the following on the command line.

    python3 analyzer.py {url}

 - Explain what these tests test and why

   This will allow you to parse a url along with the program. What the program will fetch for you in
   return will be the title of the article and 'a' tags that are found within this url in a JSON
   format. It will look like the folloeing format

    {article title name}
    {[list of urls in JSON format]}

    Example:
    python3 model.py https://www.cnn.com/politics/live-news/impeachment-inquiry-10-21-2019/index.html

    The latest on the Trump impeachment inquiry: live updates - CNNPolitics

    {You can see the list of URLs for yourself}

Authors

    Adrian Gutierrez - Initial work

Acknowledgments

    A tip of the cap to those who worked hard on this project