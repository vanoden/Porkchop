import session

# add a new monitor collection sensors
params = {'method': 'addCollectionSensor',
             'collection_code' : 'ABCD1234',
             'asset_code' : 'RPI Temp',
             'sensor_code' : 'ABCD1234',
             'name' : 'RPI Temp',
             'btn_submit' : 'Submit'}

session.pp.pprint (session.HTTPPost('/_monitor/api', params)
