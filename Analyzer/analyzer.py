<<<<<<< HEAD
import pandas as pd
from sklearn.preprocessing import MinMaxScaler
from sklearn.model_selection import train_test_split
from sklearn.metrics import classification_report, confusion_matrix
from sklearn.ensemble import GradientBoostingClassifier

# Load training data
train_data = pd.read_csv("clickbait_data.csv")
test_data = pd.read_csv("articles.csv")

# dataframe 
y_train = train_data["Clickbait"]
train_data.drop(labels="Clickbait", axis=1, inplace=True)

# create concatenated data
full_data = train_data.append(test_data, sort=True)

# drop colund that won't be necessary for training
drop_columns = ["Num"]
full_data.drop(labels=drop_columns, axis=1, inplace=True)

# text data will be converted into numbers
full_data = pd.get_dummies(full_data, columns=["Title"])
full_data.fillna(value=0.0, inplace=True)

# split data into training and test
X_train = full_data.values[0:82]
X_test = full_data.values[0:45]

# scale data by creating and instance 
scaler = MinMaxScaler()
X_train = scaler.fit_transform(X_train)
X_test = scaler.transform(X_test)

#set a seed and percentage of data
state = 12
test_size = 0.30

X_train, X_val, y_train, y_val = train_test_split(X_train, y_train,
        test_size=test_size, random_state=state)

# try setting different learning rates
lr_list = [0.05, 0.075, 0.1, 0.25, 0.5, 0.75, 1]

for learning_rate in lr_list:
    gb_clf = GradientBoostingClassifier(n_estimators=20, learning_rate=learning_rate, max_features=2,
            max_depth=2, random_state=0)
    gb_clf.fit(X_train, y_train)
    print("Learning Rate: ", learning_rate)
    print("Accuracy score (training): {0:.3f}".format(gb_clf.score(X_train, y_train)))
=======
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
>>>>>>> 9ba7af88245e0df13350e765fcb9407bae220317
