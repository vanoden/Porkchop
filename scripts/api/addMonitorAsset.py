import session

# add a new monitor asset
params = {'method': 'addAsset',
             'asset_id' : 1,
             'asset_code' : 'KEV-RPI-TEM',
             'company_id': session.config.company_id,
             'asset_name' : 'Raspberry Pi Zero W w/DHT 11',
             'organization_id': session.config.organization_id,
             'product_id' : 1,
             'distributor_id' : 0}

session.pp.pprint (session.HTTPPost('/_monitor/api', params))
