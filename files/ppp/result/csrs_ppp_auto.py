#!/usr/bin/env python3

# ---------------------------------------------------------------------------------------------------- #
# Use of Canadian Geodetic Survey products and data is subject to the Open Government Licence - Canada #
# https://open.canada.ca/en/open-government-licence-canada                                             #
# ---------------------------------------------------------------------------------------------------- #

# ----------------------------------------------------------------------------------------------------------------------
# NAME: csrs_ppp_auto.py
# FUNCTION: To replace the use of a browser and desktop application for submission of RINEX files to CSRS-PPP
#
# INSTRUCTIONS
# ------------
# 0) Requirements:
#       Python v3.5.x or higher (https://www.python.org/)
#       Requests library, v2.18.1 or higher (http://docs.python-requests.org/en/master/)
#       Requests Toolbelt library, v0.8.0 or higher (https://toolbelt.readthedocs.io/en/latest/)
#
# 1) On the command line:
#   => csrs_ppp_auto.py --user_name first.last@email.com --lang en --mode Static --ref ITRF --epoch CURR --vdatum CGVD2013 --rnx rnxfile --results_dir dest_dir --email first.last@email.com --get_max 30
#   where:
#       rnxfile is the full path of RINEX file, and looks like
#           C:/Users/{username}/ABCD/ALGO2390.15o
#               or
#           C:/Users/{username}/ABCD/ALGO2390.15d.Z
#               or
#           $/home/{username}/xyz/ALGO2390.15o
#
#       and
#
#       dest_dir is the absolute path of the directory in which you want the results saved
#
#   The following arguments have defaults:
#       --lang      (default="en")
#       --mode      (default="Static")
#       --ref       (default="NAD83")
#       --epoch     (default="CURR")
#       --vdatum    (default="CGVD2013")
#       --email     (default="dummy_email"; results not received via email)
#       --get_max   (default=30)
#
#   Minimalist command:
#   => csrs_ppp_auto.py --user_name first.last@email.com --rnx rnxfile --results_dir dest_dir
#
# 2) If you find that the script keeps timing out, try increasing the value of --get_max.
#
# 3) Use --web command line flag to visit CSRS-PPP website (provided a web browser is installed).
#
# CHANGELOG
# ---------
# DATE          WHO						DESCRIPTION
# 2018-12-07    Justin Farinaccio		1.3.3
# Modified:
#   2018-12-07 - 1.3.3 - JF
#       Added web as an optional command line flag
#   2018-11-30 - 1.3.2 - JF
#       Made get_max an optional command line argument
#   2018-07-16 - 1.3.1 - JF
#       Made return email an argument and results_dir optional, but at least one of the two must be provided
#   2018-06-29 - 1.3.0 - JF
#       Added vertical datum as input parameter
#       Added validation for results directory
#       Added instructions for receiving results via email
#   2018-05-24 - 1.2.1 - JF
#       Added earliest destination epoch (1994-01-01) validation
#   2018-03-06 - 1.2.0 - JF
#       rnx argument is now full path of RINEX file
#       results_dir argument is now directory that results are saved in
#       Arguments lang, ref, epoch, mode now have defaults
#       Removed use of wget and unzip tools, replaced with Requests and zipfile Python libraries
#   2018-03-01 - 1.1.2 - JF
#       Ensures minimum Python version of 3.5.x
#   2018-01-22 - 1.1.1 - JF
#       Added license header
#   2017-11-02 - 1.1.0 - JF
#       Made working directory a command line argument
#       Added verification for command line arguments lang, ref, mode
#       Added epoch verification
#       Used new string formatting
#   2017-07-25 - 1.0.0 - JF
#       Initial version
# ----------------------------------------------------------------------------------------------------------------------

"""To replace the use of a browser and desktop application for submission of RINEX files to CSRS-PPP"""

# Script info
# -----------
__author__ = "Justin Farinaccio"
__copyright__ = (
    "Copyright (c) 2017-2018, Justin Farinaccio,"
    "\n\tCanadian Geodetic Survey, Surveyor General Branch,"
    "\n\tNatural Resources Canada"
    "\nAll Rights Reserved."
)

# Imports
# -------
import os
import sys
import time
import argparse
import datetime
import shutil
import zipfile
import errno
import webbrowser

try:
    # assert sys.version_info >= (3, 5)
    assert sys.version_info >= (3, 4)
except AssertionError:
    sys.exit("ERROR: Must use Python 3.5.x or higher\n\t(see https://www.python.org/)")
try:
    import requests
except ImportError:
    sys.exit(
        "ERROR: Must install Requests library\n\t(see http://docs.python-requests.org/en/master/)"
    )
try:
    from requests_toolbelt.multipart.encoder import MultipartEncoder
except ImportError:
    sys.exit(
        "ERROR: Must install Requests Toolbelt library\n\t(see https://toolbelt.readthedocs.io/en/latest/)"
    )


# Read/set options
# ----------------
# Instantiate the parser
parser = argparse.ArgumentParser(
    description="{0}".format(
        "# ---------------- Using CSRS-PPP via script, version 1.3.3 ---------------- #"
    ),
    epilog="{0}".format(
        "# -------------- See script header for complete documentation -------------- #"
    ),
)
# Positional arguments
parser.add_argument(
    "--user_name",
    type=str,
    required=True,
    help="User name (email) / Utilisateur (e-mail)",
)
parser.add_argument(
    "--lang",
    nargs="?",
    const=1,
    default="en",
    type=str.lower,
    choices=["en", "fr"],
    help='Language / Langue (default="en")',
)
parser.add_argument(
    "--mode",
    nargs="?",
    const=1,
    default="Static",
    type=str.capitalize,
    choices=["Static", "Statique", "Kinematic", "Cinematique", "Cin\u00e9matique"],
    help='Processing mode / Mode de traitement (default="Static")',
)
parser.add_argument(
    "--ref",
    nargs="?",
    const=1,
    default="NAD83",
    type=str.upper,
    choices=["NAD83", "ITRF"],
    help='Reference frame / Cadre de r\u00e9f\u00e9rence (default="NAD83")',
)
parser.add_argument(
    "--epoch",
    nargs="?",
    const=1,
    default="CURR",
    type=str.upper,
    help='NAD83 epoch / \u00e9poque (format="YYYY-MM-DD") (default="CURR")',
)
parser.add_argument(
    "--vdatum",
    nargs="?",
    const=1,
    default="CGVD2013",
    type=str.upper,
    choices=["CGVD2013", "CGVD28"],
    help='Vertical datum / Datum altim\u00e9trique (default="CGVD2013")',
)
parser.add_argument(
    "--rnx",
    type=str,
    required=True,
    help="Absolute path of RINEX observation file / "
    "Chemin d'acc\u00e8s du fichier d'observation RINEX",
)
parser.add_argument(
    "--results_dir",
    type=str,
    help="Absolute path of directory in which to save results / "
    "Chemin d'acc\u00e8s du r\u00e9pertoire dans lequel enregistrer les r\u00e9sultats",
)
parser.add_argument(
    "--email",
    nargs="?",
    const=1,
    default="dummy_email",
    type=str,
    help="Send results to this email / Envoyer les r\u00e9sultats \u00e0 ce courriel "
    '(default="dummy_email" (results downloaded to directory))',
)
parser.add_argument(
    "--get_max",
    nargs="?",
    const=1,
    default=30,
    type=int,
    help="Number of 10-second intervals to wait for results / "
    "Nombre d'intervalles de 10 secondes d'attendre des r\u00e9sultats (default=30)",
)
parser.add_argument(
    "--web",
    action="store_true",
    help="Visit CSRS-PPP website (flag) / Visitez le site Web SCRS-PPP (option)",
)
# Parse arguments
args = parser.parse_args()

# Launch CSRS-PPP web page
# ------------------------
if args.web:
    try:
        if args.lang == "en":
            print("Loading CSRS-PPP website...")
        elif args.lang == "fr":
            print("Chargement du site Web SCRS-PPP...")
        time.sleep(3)
        webbrowser.open(
            "https://webapp.geod.nrcan.gc.ca/geod/tools-outils/ppp.php?locale={0}".format(
                args.lang
            ),
            new=2,
            autoraise=True,
        )
        sys.exit()
    except webbrowser.Error:
        sys.exit("ERROR: Cannot load web page")

# Verify method(s) to return results to user
# ------------------------------------------
if args.email == "dummy_email" and not args.results_dir:
    sys.exit("ERROR: Must provide directory or an email address to receive results")

if args.email != "dummy_email" and "@" not in args.email:
    sys.exit("ERROR: Please enter valid email")

# Verify date format
# ------------------
if "CURR" not in args.epoch and "COUR" not in args.epoch:
    # min_date = datetime.datetime.strptime('1997-01-01', '%Y-%m-%d').date()
    max_date = datetime.date.today()
    try:
        input_date = datetime.datetime.strptime(args.epoch, "%Y-%m-%d").date()
        if input_date < datetime.date(1994, 1, 1):
            sys.exit(
                "ERROR: Invalid epoch: {0:s}\n\tPlease select an epoch that is no earlier than 1994-01-01".format(
                    args.epoch
                )
            )
    except ValueError:
        sys.exit(
            "ERROR: Invalid epoch: {0:s}\n\tFormat must be: YYYY-MM-DD".format(
                args.epoch
            )
        )

    if input_date > max_date:
        sys.exit(
            "ERROR: Epoch cannot be later than today's date:"
            "\n\tepoch chosen: {0:s}\n\ttoday's date: {1}".format(args.epoch, max_date)
        )

# Verify get_max
# --------------
if args.get_max < 0:
    sys.exit("ERROR: get_max cannot be negative...")
if 0 <= args.get_max < 30:
    sys.exit(
        "ERROR: As get_max is the number of 10-second intervals to wait for results,"
        "\n\tplease wait at least 5 minutes to receive your results."
    )

# Working directory
# -----------------
rinex_file_abspath = os.path.abspath(args.rnx)

(rinex_path, rinex_file) = os.path.split(rinex_file_abspath)

try:
    os.chdir(rinex_path)
except FileNotFoundError:
    sys.exit("ERROR: Cannot access directory {0:s}".format(rinex_path))

# Useful var
# ----------
procstat = None

# Local Vars
# ----------
error = 0
debug = 0
sleepsec = 10

request_max = 5
# get_max = 30

keyid = None

# Specify the URL of the page to post to
# --------------------------------------
url_to_post_to = "https://webapp.geod.nrcan.gc.ca/CSRS-PPP/service/submit"
browser_name = "CSRS-PPP access via Python Browser Emulator"

# Define PPP access mode
# ----------------------
ppp_access = "nobrowser_status"  # default starting 2013-09-30

# Print Info
# ----------
# Ensure splitting up the absolute path produces a file name and not just a directory
if rinex_file is None:
    sys.exit("ERROR: No RINEX file")
(rinex_name, suffix) = os.path.splitext(rinex_file)

print("=> RNX: {0:s} [{1:s}]".format(rinex_file, rinex_path))

# Some more initializations
# -------------------------
if args.mode == "Static" or args.mode == "Statique":
    process_type = "Static"
else:
    process_type = "Kinematic"

if args.epoch == "CURR" or args.epoch == "COUR":
    nad83_epoch = "CURR"
else:
    nad83_epoch = args.epoch

if args.ref == "ITRF":
    if args.vdatum == "CGVD28":
        print(
            "\nNOTE:\tReference frame of ITRF only allows vertical datum of CGVD2013"
            "\n\tVertical datum set to CGVD2013\n"
        )
        time.sleep(10)
    vdatum = "cgvd2013"

    if nad83_epoch != "CURR":
        print(
            "\nNOTE:\tReference frame of ITRF only allows the epoch to be the same as the GPS data"
            '\n\tEpoch set to "CURR"\n'
        )
        time.sleep(10)
        nad83_epoch = "CURR"
else:
    vdatum = args.vdatum.lower()

# -------------------------------------------------
# Create the browser that will post the information
# -------------------------------------------------
# ITERATE up to request_max!

for request_num in range(request_max):
    print("=> Request_num[{0:d}]".format(request_num))

    # The information to POST to the program
    content = {
        "return_email": args.email,
        "cmd_process_type": "std",
        "ppp_access": ppp_access,
        "language": args.lang,
        "user_name": args.user_name,
        "process_type": process_type,
        "sysref": args.ref,
        "nad83_epoch": nad83_epoch,
        "v_datum": vdatum,
        "rfile_upload": (rinex_file, open(rinex_file, "rb"), "text/plain"),
    }
    mtp_data = MultipartEncoder(fields=content)
    # Insert the browser name, if specified
    header = {
        "User-Agent": browser_name,
        "Content-Type": mtp_data.content_type,
        "Accept": "text/plain",
    }

    req = requests.post(url_to_post_to, data=mtp_data, headers=header)
    keyid = str(req.text)  # The keyid required for the job

    my_req = requests.get(url_to_post_to)

    # Print some values for debug!
    if debug:
        print("Key: {0:s}".format(req.text))

    # 'key'
    if req.text:
        keyid = req.text

        # Problems while doing the 'request'
        # ----------------------------------
        # Re-Submit!
        if "DOCTYPE" in keyid:
            print("=> Hash{{_content}} has a weird value! [{0:s}]".format(rinex_name))
            if debug:
                print("{0:s}".format(keyid))
            print("=> Re-Submit ...")
            continue
        # key OKAY!
        # ---------
        print("=> Keyid: {0:s}".format(keyid))
        print('=> Now wait until "Status=done" ...')

        # All is OKAY... now wait!
        # ------------------------
        # -----------------
        # Loop until "done"
        # -----------------
        get_num = 0
        if os.path.isfile("Status.txt"):
            os.remove("Status.txt")

        while not (procstat == "done" or error == 1):
            get_num += 1

            # Check get_max
            if get_num > args.get_max:
                print(
                    "=> Max number of get [{0:d}] exceeded! Too long! [{1:s}]".format(
                        args.get_max, rinex_name
                    )
                )
                print("=> Next file ...")
                error = 1
                sys.exit(error)

            # get 'Status.txt'
            r = requests.get(
                'https://webapp.geod.nrcan.gc.ca/CSRS-PPP/service/results/status?id={0:s}"'.format(
                    keyid
                ),
                timeout=5,
            )
            with open("Status.txt", "wb") as f:
                f.write(r.content)

            # Check 'Status.txt'
            if os.path.isfile("Status.txt"):
                with open("Status.txt") as f:
                    status = f.readlines()

                # Read 'Status.txt'
                if "processing" in str(status).lower():
                    procstat = "processing"
                    sleepmore = 1
                elif "done" in str(status).lower():
                    procstat = "done"
                    sleepmore = 0
                elif "error" in str(status).lower():
                    procstat = "error"
                    sleepmore = 1
                else:
                    procstat = "Unknown"
                    print("*ERR*[{0:d}] ... log content follows ...".format(get_num))
                    print("{0:s}".format(status))
                    sleepmore = 1

                print(
                    "\tStatus[{0:d}]: {1:s} [{2:s}]".format(
                        get_num, procstat, rinex_file
                    )
                )

                if sleepmore:
                    print("\t[sleep {0:d} sec]".format(sleepsec))
                    time.sleep(sleepsec)
            # Re-get!
            else:
                print(
                    '=> get[{0:d}]: "Status.txt" *NOT* found! get again!'.format(
                        get_num
                    )
                )

        # ---------------------------------
        # Get result file 'full_output.zip'
        # ---------------------------------
        if not error:
            result_name = "full_output.zip"
            if os.path.isfile(result_name):
                os.remove(result_name)
            print("=> Get results file: {0:s}".format(result_name))

            # Iterate!
            # --------
            for get_num in range(args.get_max):
                # get
                r = requests.get(
                    "https://webapp.geod.nrcan.gc.ca/CSRS-PPP/service/results/file?id={0:s}".format(
                        keyid
                    ),
                    timeout=5,
                )
                with open(result_name, "wb") as f:
                    f.write(r.content)

                if os.path.isfile(result_name):
                    print(
                        '=> Okay[{0:d}]: Got file "{1:s}" ... Moved to {2:s} ... if OK!'.format(
                            get_num, result_name, rinex_name + "_" + result_name
                        )
                    )

                    # Check zip integrity
                    try:
                        zip_ref = zipfile.ZipFile(result_name).testzip()
                    except zipfile.BadZipFile:
                        sys.exit("ERROR: Bad ZIP file")

                    print("=> Integrity[{0:d}]: OK.".format(get_num))
                    os.rename(result_name, rinex_name + "_" + result_name)
                    result_name = rinex_name + "_" + result_name

                    # Extract sum and PDF
                    # -------------------
                    for extract_name in zipfile.ZipFile(result_name, "r").namelist():
                        if ".sum" in extract_name or ".pdf" in extract_name:
                            print("\textracting ... {0:s}".format(extract_name))

                            with zipfile.ZipFile(result_name, "r") as zip_ref:
                                zip_ref.extract(extract_name)

                            # Make results_dir if it does not exist
                            try:
                                os.makedirs(args.results_dir)
                            except OSError as e:
                                if e.errno != errno.EEXIST:
                                    raise
                            # Move outputs to desired path
                            shutil.move(
                                extract_name,
                                "{0}/{1}".format(args.results_dir, extract_name),
                            )

                    # Move zip to desired path
                    shutil.move(
                        result_name, "{0}/{1}".format(args.results_dir, result_name)
                    )

                    sys.exit()
                else:
                    print('** Error: File "$result_name" *NOT* found!')
                    print("=> Will Re-get ...")
                    continue  # re-get!

            # Max get reached?
            # ----------------
            print("=> Max number of requests [{0:d}] exceeded!".format(args.get_max))
            print("=> RNX: {0:s} [keyid: {1:s}]".format(rinex_name, keyid))
            print("=> NEXT file!")
            error = 1
            sys.exit(error)
        else:
            print("=> NO results! An error occurred while processing!!!")
            print("=> RNX: {0:s} [keyid: {1:s}]".format(rinex_name, keyid))
            error = 1
            sys.exit(error)

    # Problems while doing the 'request'
    # ----------------------------------
    # Re-Submit!
    else:
        print("=> Hash{_content} does *NOT* exist!")
        print("=> Re-Submit ...")
        continue

# if it gets here => Problems ... NEXT file!
# ------------------------------------------
print(
    "=> Max number of requests [{0:d}] exceeded! [{1:s}]".format(
        request_max, rinex_name
    )
)
print("=> Next file ...")
sys.exit(1)
