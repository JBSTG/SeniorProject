from lxml.html import fromstring
import urllib
import random
import requests
import sys

# Open the file to read
dataFile = open('clickbait_data.csv', encoding='utf-8')

# read the data from file into a list
listOfLines = dataFile.read().splitlines()

# set line starting from line 1 of csv file
aLine = listOfLines[1]
# when printed list will look like following:
# ['Num', 'Clickbait', 'Title']
lineItems = aLine.split(',')

sum = 0
count = 0
listOfAverages = []
for i in range(1, 10, 1):
    aLine = randlistOfLines[i]
    lineItems = aLine.split(',')
    # set id to Num column
    id = lineItems[0]
    # set data to clickbait column
    data = lineItems[len(lineItems)-2]
    count = count + 1
    # sum of data
    sum = sum + float(data)
    avg = sum / count
    #print('id=', id, 'sum=', sum, 'avg=', avg)
    #subList = [count, avg]
    #listOfAverages.append(subList)

print('Score: {:0.2f}\n'.format(avg))

# function will grab title and all links in url
def grabURL(url):
    r = requests.get(url)
    tree = fromstring(r.content)
    # prints article title
    print (tree.findtext(".//title"), "\n")
    # prints list of links in url
    print (tree.xpath("//a/@href"))
try:
    # takes in url argument
    grabURL(sys.argv[1])
except:
    # inform you that no argument has been provided
    print ('No URL in command line')
    sys.exit(1)