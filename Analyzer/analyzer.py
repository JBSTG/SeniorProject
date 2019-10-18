#!/usr/bin/python3
from lxml.html import fromstring
import urllib
import requests
import sys

'''
# Open the file to read
dataFile = open('clickbait_data.csv', encoding='utf-8')

# read the data from file into a list
listOfLines = dataFile.read().splitlines()
aLine = listOfLines[1]
lineItems = aLine.split(',')
print(lineItems)
value = lineItems[1]

sum = 0
count = 0
listOfAverages = []
output = int(float(value))

for i in range(1, len(listOfLines), 1):
    aLine = listOfLines[i]
    lineItems = aLine.split(',')
    value = lineItems[1]
    count = count + 1
    sum = sum + output
    avg = sum / count
    if i == 82:
        subList = [avg]
        listOfAverages.append(subList)
        sum = int(float(value))
        count = 1
for i in range(0, len(listOfAverages), 1):
    print(listOfAverages[i])
'''

# function will grab title and all links in url
def grabURL(url):
    r = requests.get(url)
    tree = fromstring(r.content)
    print (tree.findtext(".//title"))
    print (tree.xpath("//a/@href"))

grabURL(sys.argv[1])