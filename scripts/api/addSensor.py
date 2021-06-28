import session

# add a new monitor sensor
params = {'method': 'addSensor',
             'code' : 'Kevin Monitor Sensor',
             'asset_code' : 'ABC1234',
             'btn_submit' : 'Submit',
             }

session.pp.pprint (session.HTTPPost('/_monitor/api', params)
