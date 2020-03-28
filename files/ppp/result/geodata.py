import pickle

with open("ramsac.pickle", "rb") as f:
    ramsac = pickle.load(f)

with open("sws.pickle", "rb") as f:
    sws = pickle.load(f)


def get_ramsac_wk(wk):
    return [ep for ep in ramsac if ep in sws[wk]]
