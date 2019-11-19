from lxml.html import fromstring
import urllib
import random
import re
import requests
import sys


class Word:
    content = ""
    cpCB = 0.0
    cpNO = 0.0
    cbQty = 0
    noQty = 0

    def __init__(self,content):
        self.content = content

class DocumentManager:
    tDocQty = 0
    cbQty = 0
    noQty = 0
    trainingDocuments = []
    vocabSize = 0
    vocabSizeCB = 0
    vocabSizeNO = 0
    totalVocab = {}

    def addDocument(self,isClickbait):
        if(isClickbait==1):
            self.cbQty+=1
        else:
            self.noQty+=1
        self.tDocQty+=1
    
    def parseDocument(self,document,isClickbait):
        words = document.split(" ")
        for word in words:
            if(word not in self.totalVocab):
                newWord = Word(word)
                if(isClickbait):
                    newWord.cbQty+=1
                    self.vocabSize+=1
                    self.vocabSizeCB+=1
                else:
                    newWord.noQty+=1
                    self.vocabSize+=1
                    self.vocabSizeNO+=1
                self.totalVocab[newWord.content]=newWord
            else:
                if(isClickbait):
                    self.totalVocab[word].cbQty+=1
                    self.vocabSizeCB+=1
                else:
                    self.totalVocab[word].noQty+=1
                    self.vocabSizeNO+=1
    def createConditionalProbabilities(self):
        for word in self.totalVocab:
            self.totalVocab[word].cpCB = (self.totalVocab[word].cbQty+1)/(self.vocabSize+self.vocabSizeCB)
            self.totalVocab[word].cpNO = (self.totalVocab[word].noQty+1)/(self.vocabSize+self.vocabSizeNO)
        '''
        for words in self.totalVocab:
            w = self.totalVocab[words]
            print(w.content+" "+str(w.cpCB))
        '''
    def returnAnalysis(self,title):
        words = title.split(" ")
        pCB = self.cbQty/self.tDocQty
        pNO = self.noQty/self.tDocQty
        for word in words:
            if word in self.totalVocab:
                pCB=pCB*self.totalVocab[word].cpCB
        for word in words:
            if word in self.totalVocab:
                pNO=pNO*self.totalVocab[word].cpNO
        if pCB>pNO:
            print("1")
            '''
            print(str(1-(pNO/pCB))
            print("TITLE: "+title)
            print(str(pNO))
            print(str(pCB))
            '''
        else:
            print("0")
            '''
            print(str(1-(pCB/pNO)))
            print("TITLE: "+title)
            print(str(pNO))
            print(str(pCB))
            '''
        print(title)



def getTitleFromURL(url):
    try:
        r = requests.get(url)
    except:
        return "Website Unavailable (Exception Encountered)"
    if r.status_code != 200:
        return "Website Unavailable"
    
    tree = fromstring(r.content)
    # article title
    title = tree.findtext(".//title")
    return title

# look for title in csv file and return score if
# available, else return -1
def check_title(title,lineItems):
    with open("clickbait_data.csv", "r") as f:
        s = f.read()
        data = lineItems[len(lineItems)-2]
        if title not in s:
            # not in database
            print("Score:", -1, "\n")
            # percent_match(regex, title)
        else:
            # will print clickbait score of article
            print("Score:", data, "\n")



# print("Score: {:0.2f}\n".format(avg))

# pattern:   number              noun                      verb             word char
# regex = "(?:\s*\d+)*(?:\b[A-Z][A-Za-z0-9]+\b)(?:\s*\b[A-Z][A-Za-z0-9]+\b)(?:\s+\w++)?"
# def return score(avg, current_score)
#   function will calculate the score
#   to ifentify whether the article
#   is clickbait

# function will give approximate match percentage
# this is known as fuzzy string matching
def percent_match(regex, target):
    left = 0
    right = len(target) - 1
    current = right / 2
    while left < right:
        if regex.match(target[left:right]):
            left = current
        else:
            right = current
        current = (right - left) / 2
    return m / len(target)
    #return_score(avg, m)

# function will grab title and all links in url
def grabURL(url,lineItems):
    r = requests.get(url)
    tree = fromstring(r.content)
    # article title
    title = tree.findtext(".//title")
    # list of links in url
    ahref = tree.xpath("//a/@href")
    # call function to check for title
    check_title(title,lineItems)
    # prints article title
    print("Title:", title, "\n")
    # prints list of links in url
    print(ahref)
