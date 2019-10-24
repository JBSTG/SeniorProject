from lxml.html import fromstring
import urllib
import random
import requests
import sys

# Open the file to read
dataFile = open("clickbait_data.csv", encoding="utf-8")

# read the data from file into a list
listOfLines = dataFile.read().splitlines()

# set line starting from line 1 of csv file
aLine = listOfLines[1]

# when printed list will look like following:
# ['Num', 'Clickbait', 'Title']
lineItems = aLine.split(",")
# initialize
sum = 0
count = 0
listOfAverages = []
for i in range(1, 10, 1):
    aLine = listOfLines[i]
    lineItems = aLine.split(",")
    # set id to Num column
    id = lineItems[0]
    # set data to clickbait column
    data = lineItems[len(lineItems)-2]
    count = count + 1
    # sum of data
    sum = sum + float(data)
    avg = sum / count
    #print("id=", id, "sum=", sum, "avg=", avg)
    #subList = [count, avg]
    #listOfAverages.append(subList)

#print("Score: {:0.2f}\n".format(avg))

# look for title in csv file and return score if
# available, else return -1
def check_title(title):
    with open("clickbait_data.csv", "r") as f:
        s = f.read()
        if title not in s:
            # not in database
            print("Score:", -1, "\n")
        else:
            # will print clickbait score of article
            print("Score:", 0, "\n")

# function will grab title and all links in url
def grabURL(url):
    r = requests.get(url)
    tree = fromstring(r.content)
    # articletitle
    title = tree.findtext(".//title")
    # list of links in url
    ahref = tree.xpath("//a/@href")
    # call function to check for title
    check_title(title)
    # prints article title
    print("Title:", title, "\n")
    # prints list of links in url
    print(ahref)

try:
    # takes in url argument
    grabURL(sys.argv[1])
except:
    # inform you that no argument has been provided
    print("No URL in command line")
    # exit 
    sys.exit(0)