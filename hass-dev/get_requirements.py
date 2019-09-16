import re

with open("setup.py") as file:
  data = file.read()

reqs = re.search( r'REQUIRES[\s]=[\s]\[(.*?)\]', data, re.S|re.I)
pattern = re.compile(r'["\'](.*?)["\'],')
result = pattern.findall(reqs.group(1))

with open("requirements.txt","w") as f:
    for item in result:
        f.write(item+'\n')
