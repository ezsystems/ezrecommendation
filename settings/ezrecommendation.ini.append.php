<?php /* #?ini charset="utf8"?
# Choose the ezrecommendation solution for your site between shop and publisher
# Do not forget to clear template cache if you change the solution
[SolutionSettings]
solution=publisher

#IMPORTANT for the shop solution
#Set your default currency if it is not defined in Web-Shop Backend
[ShopPriceCurrency]
defaultCurrency=EUR

# Insert here your ezrecommendation costumerid and your licence key.
# You can register or recover password and license key under https://admin.yoochoose.net
[ClientIdSettings]
CustomerID=UNKNOWN
LicenseKey=UNKNOWN


# If enabled, the requests to the ezrecommendation engine will be received and debugged in the ez debug.log in var/log.
[RequestSettings]
ReceiveAnswer=enabled

[BulkExportSettings]
# The URL of your Site. If you have a www-Dir, you should also add it. You can copy the URL, with which you load the XML in your browser.
SiteURL=
# Change this only if you patch the initial export script
BulkPath=
# Default bulk XML entrys
XmlEntrys=1000
#################################################################
# The following are settings which are controlled by yoochoose	#
# and should only be changed in consultation with yoochoose.	#
#################################################################

# The Urls to the yoochoose engine.
[URLSettings]
RequestURL=event.yoochoose.net
RecoURL=reco.yoochoose.net
ExportURL=admin.yoochoose.net
ConfigURL=admin.yoochoose.net

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
numrecs=numrecs

# Maps the site or item context to the yoochoose solution name.
[SolutionMapSettings]
publisher=news
shop=ebl

# The format of the recommendations from the engine.
# Until now json is supported.
[ExtensionSettings]
usedExtension=json

[RecommendationSettings]
DefaultScenario=top_clicked

#used by flow block to fill scenario selection box
[BackendSettings]
AvailableScenarios[top_clicked]=Top clicked content
AvailableScenarios[top_clicked_article]=Top clicked articles
AvailableScenarios[also_clicked_article]=Also clicked articles
AvailableScenarios[also_clicked_article_global]=Globally also clicked articles
AvailableScenarios[also_clicked_image]=Also clicked images with articles
AvailableScenarios[also_clicked_media]=Also clicked media with articles
AvailableScenarios[also_clicked_ugc]=Also clicked user generated content with articles
AvailableScenarios[top_consumed]=Top consumed content
AvailableScenarios[top_consumed_article]=Top consumed articles
AvailableScenarios[also_consumed_article]=Also consumed article
AvailableScenarios[also_consumed_article_global]=Globally also consumed article
AvailableScenarios[also_consumed_image]=Also consumed images with articles
AvailableScenarios[also_consumed_media]=Also consumed media with articles
AvailableScenarios[also_consumed_ugc]=Also consumed user generated content with articles
AvailableScenarios[personalized1]=Personalized articles
AvailableScenarios[personalized1_image]=Personalized images with articles
AvailableScenarios[personalized1_media]=Personalized media with articles
AvailableScenarios[personalized1_ugc]=Personalized user generated content with articles
AvailableScenarios[personalized2]=Peronalized articles by Stereotype Algorithm
AvailableScenarios[top_rated]=Top rated
DefaultItemLimit=3
*/
?>
