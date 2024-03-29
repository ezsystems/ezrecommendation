eZrecommendation extension installation documentation

# Requirements
- eZ Publish 4.6 or later version
- pcntl extension
- pecl/http extension
- ezpHttpRequest extension (http://github.com/ezsystems/ezpHttpRequest)

# Installation

1. Extract and copy the ezrecommendation extension into 'extension' folder.
2. Activate ezrecommendation
   In the administration interfaceinterface, click 'setup' tab, then on 'extensions'. Select 'ezrecommendation', and click on 'Apply Changes'.
   or in settings/override/site.ini.append.php, add ezrecommendation to the list of active extensions:
       [ExtensionSettings]
       ActiveExtensions[]=ezrecommendation

3. Regenerate autoloads
   In administrator user interface, click 'setup' tab->'extensions' menu, select 'ezrecommendation', click button 'Regenerate
   autoload arrays for extensions', or in eZ Publish installation folder, run "php bin/php/ezpgenerateautoloads.php -e"

4. Allow access for the ezrecommendation module
   In administrator user interface, click 'User accounts' -> 'Roles and policies' then 'Anonymous'. Click on the Role and edit the Policies.
   Add new Policy. Choose the module 'ezrecommendation' and then grant full access to this module. Do the same for the Role 'Members' if you have a login area an your site.

5. Clear cache
   Clear INI and template caches. (from admin 'Setup' tab or commandline)

*IMPORTANT NOTICE*
The extension contains an override pagelayout template for the ezdemo and ezwebin design to ease the activation and configuration process.
The included pagelayout contains the tracking event of clicks which must be available. If you use a custom pagelayout.tpl integrate this tracking event in your custom template.
You will find the required template code in the user handbook.

# INTEGRATION OF THE PIXEL

In order to integrate the pixel you simply need to place the following code within your (customized) pagelayout.tpl inside the html-body tag:

{include uri='design:content/ezrecommendation_html.tpl' content=$module_result track=true()}

If track is set 'true' every site access will be treated as a click event. If you don't want to track every site access you will
need to integrate the 'click event' in the tag you want to be tracked.

The template must be included in any case. Otherwise the events won't work.

Be careful with placing this code inside a cache block, because it appends a sessionID to the pixel.


# INTEGRATION OF EVENTS

You have the opportunity to track special events on your site by simply integrating the following code in html-tags:

{generate_common_event($node, 'eventtype')}

The code can be placed for example inside a <a>-tag. It will produce an onclick-Event. You just have to pass the current node
and the eventtype ('click', 'recommend' and others).

For the rate-Event you need to use the special template function 'generate_rate_event', where you have to pass more variables
like the rating.



# GETTING RECOMMENDATIONS

In order to get recommendations for a specific node you can choose a custom tag called ezrecommendation in the admin backen when editing e.g. an article.

Alternatively you can include following code in the position you want the recommendations to be shown:
{include uri='design:content/recommendations.tpl'  node=$node scenario='top_selling' limit=5 track_rendered_items=true() create_clickrecommended_event=true()}

This will get recommendations for the given node. If you set 'track_rendered_items=true()', the shown recommendations, will also be tracked.
In the course of time this will produce a statistic of items a specific user been recommended, so the probability to get the same recommendations is minimized.
'create_clickrendered_event=true()' will create an onclick-event in every recommendation <a>-tag, so when the user clicks on it,
this special event will be tracked as a 'clickrecommended-event' (explanation below).

Remember to disable caching when fetching personalized recommendations! They differ from user to user.


# SETTINGS IN ezrecommendation.ini.append.php

## [SolutionSettings]
The user has to specify here the ezrecommendation solution he wants to use (shop or publisher).

## [ShopPriceCurrency]
The default currency-code of the webshop (e.g. EUR or USD)

## [ClientIdSettings]
These are the authentication string the user gets for his ez instance from the eZ Recommendation Service.
Registration can be done under https://admin.yoochoose.net

## [RequestSettings]
If enabled, the answer from the eZ Recommendation Service will be logged in debug.log.

*The following settings should only be modified in consultation with the eZ Recommendation Service.*

## [URLSettings]
Settings for the eZ Recommendation Service Server URLs.

## [ParameterMapSettings]
Maps the ez attribute names to the ezrecommendation parameters.

## [SolutionMapSettings]
Maps the type of the site to the ezrecommendation productid.

## [ExtensionSettings]
Defines the response form. Json is supported until now.


# Events

## Common

- click
  event when user clicks an item or accesses a site
- clickrecommended
  event when user clicks an item which has been recommnded from the engine
- blacklist
  user blacklists an item
- owns
  user owns an item

All events are generated by including following code:
    {generate_common_event($node, 'eventtype')}

## Rate

Generated by following code:
    {generate_rate_event($node, rating)}

Creates an onclick-event for rating an article. The rating has to be a value from 1 to 100.

## Buy

The buy-event is triggered after an order has been checked out. Therefore you need to configure the buy event workflow.
Go to the Setup-tab and then to Workflows. Choose the Standard workflow group or create your own one. In the group you
have to create a new workflow and give it a name. Add the "ezrecommendation buy object event" to your workflow.
After this you need to configure the trigger for your workflow. Go to the Setup tab and then to Triggers. Add the your
workflow to the "shop checkout before" trigger and apply the changes.
From now on every time an order is checked out ezrecommendation will track every product bought in this order.

Consume event:
Include the following code in the template you want to use the consume event:
    <div>{generate_consume_event($node)}</div>
This will just create a hidden div with some informations in it. After this go in the backend to the Setup tab and then click on Classes.
Choose the class where you want to configure the "time to trigger consume event" attribute and click on edit. Search the recommendation attribute
and insert the time in the "time to trigger consumption" field.
From now on every time a user overruns the time to trigger value on a site the consume event will be sent to the eZ Recommendation Service.


# Administrative events

## Export

The content of an object created in the ez publish backend can automatically be sent to the eZ Recommendation Service. Therefore you have to define
a workflow and a trigger which causes the workflow.
Go to the Setup tab and then to Workflows. Choose the Standard workflow group or create your own one. In the group you
have to create a new workflow and give it a name. Add the "ezrecommendation export object event" to your workflow. After this you need to configure the 
trigger for your workflow. Go to the Setup tab and then to Triggers. Add the your workflow to the "content publish before" trigger and apply the changes.

## Delete

The content of an object removed in the ez publish backend can automatically be sent to the eZ Recommendation Service. Therefore you have to define
a workflow and a trigger which causes the workflow.
Go to the Setup tab and then to Workflows. Choose the Standard workflow group or create your own one. In the group you
have to create a new workflow and give it a name. Add the "ezrecommendation delete object event" to your workflow. After this you need to configure the 
trigger for your workflow. Go to the Setup tab and then to Triggers. Add the your workflow to the "content delete before" trigger and apply the changes.
