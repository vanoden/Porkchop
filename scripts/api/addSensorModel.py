import session

# add a new XXX
params = {'method': 'addSensorModel',
             'code' : 'Temp Sensor'',
             'name' : 'Temperature Sensor',
             'units' : 'deg. celcius',
             'description' : 'RPI DHT11 Temp Sensor',
             'measures' : '',
             'data_type' : 'decimal',
             'minimum_value' : '-500',
             'maximum_value' : '500',
             'btn_submit' : 'Submit',
             }

session.pp.pprint (session.HTTPPost('/_monitor/api', params)
