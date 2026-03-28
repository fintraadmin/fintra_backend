import glob
import json

outfile = '/var/www/html/mocks/stock_recos.json'
result = {}

for f in glob.glob('mocks/stocks/*.json'):
   with open(f, 'r') as rf:
        print f
        try:
            parts=  f.split('.')
            id = parts[0]
            id =  id.replace('mocks/stocks/' , '')
            data =  json.load(rf)
            sc = data['info']['industry']
            if sc in result:
                result[sc].append(id)
            else:
                result[sc] = []
                result[sc].append(id)
        except:
            continue

with open(outfile, 'w') as of:
    json.dump(result, of)
of.close()

