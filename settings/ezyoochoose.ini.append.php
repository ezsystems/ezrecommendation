<?php /* #?ini charset="utf8"?

# The Urls to the yoochoose engine.
[URLSettings]
RequestURL=event.yoochoose.net
RecoURL=reco.yoochoose.net

# Maps the ez pulish attribute names to the yoochoose attribute names.  
[ParameterMapSettings]
class_id=itemtypeid
node_id=itemid
path_string=categorypath
user_id=userid
quantity=quantity
price=price
currency=currency
timestamp=timestamp
rating=rating
limit=numrecs

# Maps the site or item context to the yoochoose solution name.
[SolutionMapSettings]
publisher=news
shop=ebl

# Insert here your yoochoose clientid for the specific product.
[ClientIdSettings]
news=CUS_00006
ebl=10053

# The format of the recommendations from the engine.
# Until now json is supported.
[ExtensionSettings]
usedExtension=json

# If enabled, the requests to the yoochoose engine will be received and debugged in the ez debug.log in var/log.
[RequestSettings]
ReceiveAnswer=enabled

*/ ?>