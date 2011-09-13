<?php /* #?ini charset="utf8"?

# Choose the yoochoose solution for your site between shop and publisher
# Do not forget to clear template cache if you change the solution
[SolutionSettings]
solution=shop

#IMPORTANT for the shop solution
#Set your default currency if it is not defined in Web-Shop Backend
[ShopPriceCurrency]
defaultCurrency=YEN


# Insert here your yoochoose costumerid and your licence key.
[ClientIdSettings]
CustomerID=10053
LicenseKey=b0d2e712a3624d7e93725e656951c6ed

#CustomerID=CUS_00006
#LicenseKey=NEWS-831010-5469-10155-7176

# If enabled, the requests to the yoochoose engine will be received and debugged in the ez debug.log in var/log.
[RequestSettings]
ReceiveAnswer=enabled

#################################################################
# The following are settings which are controlled by yoochoose	#
# and should only be changed in consultation with yoochoose.	#
#################################################################

# The Urls to the yoochoose engine.
[URLSettings]
RequestURL=event.yoochoose.net
RecoURL=reco.yoochoose.net
ExportURL=import.yoochoose.net

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

# The format of the recommendations from the engine.
# Until now json is supported.
[ExtensionSettings]
usedExtension=json

[RecommendationSettings]
DefaultScenario=popular_clicked

*/ ?>