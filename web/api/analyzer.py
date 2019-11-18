from analyzer_lib import *
from lxml.html import fromstring
import urllib
import random
import re
import requests
import sys

# Open the file to read
#dataFile = open("clickbait_data.csv", encoding="utf-8")

# read the data from file into a list
#listOfLines = dataFile.read().splitlines()


def buildDocuments(filename):
    dataFile = open(filename,'r+', encoding="utf-8")
    listOfLines = dataFile.read().splitlines()
    dm = DocumentManager()
    for i in range(1, len(listOfLines), 1):
        line = listOfLines[i].split(',')
        isClickBait = int(line[1])
        dm.addDocument(int(isClickBait))
        dm.parseDocument(line[2],isClickBait)
        dm.createConditionalProbabilities()
    dataFile.close()
    return dm


#print(getTitleFromURL(sys.argv[1]))
dm = buildDocuments("/var/www/html/api/clickbait_data.csv")
dm.returnAnalysis(getTitleFromURL(sys.argv[1]))
'''
dm.returnAnalysis(getTitleFromURL("https://www.foxnews.com/travel/airline-passenger-feet-headrest"))
dm.returnAnalysis(getTitleFromURL("https://www.foxnews.com/world/mexico-cartel-attack-arrests-massacre-american-mothers-children"))
dm.returnAnalysis(getTitleFromURL("https://www.foxnews.com/world/bolivia-evo-morales-asylum-president-resignation"))
dm.returnAnalysis(getTitleFromURL("https://www.foxnews.com/world/connecticut-man-bench-warrant-arrest-killing-hotel-worker"))
dm.returnAnalysis(getTitleFromURL("https://www.foxnews.com/opinion/sen-lindsey-graham-michael-makovsky-us-israel-mutual-defense-treaty-needed-to-benefit-both-nations"))
dm.returnAnalysis(getTitleFromURL("https://www.buzzfeed.com/noradominick/avengers-endgame-peter-tony-pepper-reunion-deleted-scene?origin=nofil"))
dm.returnAnalysis(getTitleFromURL("https://www.buzzfeed.com/crystalro/mandalorian-episode-1-ending?origin=nofil"))
dm.returnAnalysis(getTitleFromURL("https://www.buzzfeed.com/noradominick/avengers-endgame-katherine-langford-deleted-scene?origin=nofil "))
dm.returnAnalysis(getTitleFromURL("https://www.buzzfeed.com/laurengarafano/gen-z-tv-character-quiz?origin=nofil"))
dm.returnAnalysis(getTitleFromURL("https://www.buzzfeed.com/kaylayandoli/movie-clapbacks-from-women-that-are-straight-up-savage?origin=nofil    "))
dm.returnAnalysis(getTitleFromURL("https://www.buzzfeed.com/alliehayes/sex-scenes-behind-the-scenes-stories?origin=nofil"))
'''
'''
# set line starting from line 1 of csv file
aLine = listOfLines[1]

# when printed list will look like following:
# ['Num', 'Clickbait', 'Title']
lineItems = aLine.split(",")


# initialize
sum = 0
count = 0
listOfAverages = []

for i in range(1, len(listOfLines), 1):
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

try:
    # takes in url argument
    grabURL(sys.argv[1],lineItems)
except:
    # inform you that no argument has been provided
    print("No URL in command line")
    # exit 
    sys.exit(1)
'''
