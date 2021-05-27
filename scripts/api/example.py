#!/usr/bin/python3

import requests

# GET Request
r = requests.get('http://localhost:8000/_api')

# POST Request
r = requests.post('http://localhost:8000/_api', data = {'key':'value'})
