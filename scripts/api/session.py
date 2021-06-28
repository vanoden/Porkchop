import http.client, urllib.parse
import pprint
import config
import pprint
pp = pprint.PrettyPrinter(indent=2)

def login():
    '''get login session to continue with an API call to add/edit Spectros information'''
    params = urllib.parse.urlencode({'method': 'authenticateSession', 'login': config.login, 'password': config.password, 'btn_submit': 'Submit'})
    headers = {"Content-type": "application/x-www-form-urlencoded","Accept": "text/plain"}
    conn = http.client.HTTPConnection(config.website)
    conn.request("POST", "/_register/api", params, headers)
    response = conn.getresponse()
    data = response.read()
    responseHeaders = response.getheaders()
    return [responseHeaders, data]
    
def HTTPPost(endpoint, params):
    '''standard HTTP POST function to API with the URI and params needed'''
    params = urllib.parse.urlencode(params)
    headers = {"Content-type": "application/x-www-form-urlencoded","Accept": "text/plain"}
    conn = http.client.HTTPConnection(config.website)
    conn.request("POST", endpoint, params, headers)
    response = conn.getresponse()
    data = response.read()
    responseHeaders = response.getheaders()
    return [responseHeaders, data]
