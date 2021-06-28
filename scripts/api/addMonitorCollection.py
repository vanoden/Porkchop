import session

# add a new collection for the entire year of 2021
params = {'method': 'addCollection',
            'code': 'ABCD1234',
            'organization_id': session.config.organization_id,
            'date_start': '2021-01-01 00:00:00',
            'date_end': '2022-01-01 00:00:00',
            'status': 'ACTIVE',
            'btn_submit': 'Submit'}

session.pp.pprint (session.HTTPPost('/_monitor/api', params))
