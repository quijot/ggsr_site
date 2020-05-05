import pickle


with open("ramsac.pickle", "rb") as f:
    ramsac = pickle.load(f)


with open("iws.pickle", "rb") as f:
    iws = pickle.load(f)


with open("sws.pickle", "rb") as f:
    sws = pickle.load(f)
