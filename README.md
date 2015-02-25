# WHMCS-Linode-DNS-Addon
Auto creation of dns zones, client access to dns zones

You can donate to our open source projects by going to: http://purdydesigns.com/en/Open-Source-Donation

In order to use this addon you will need a few things.

1. You will install the following PEAR modules into PHP
<pre>
$ sudo pear install Net_URL2-0.3.1
$ sudo pear install HTTP_Request2-0.5.2
$ sudo pear channel-discover pear.keremdurmus.com
$ sudo pear install krmdrms/Services_Linode
</pre>

2. You will need to create an API key at linode within your linode account.
I suggest you create a second user that only has access to DNS related functions.

3. Copy the entire linodedns folder into your WHMCS installation
in the directory /modules/addons

If your WHMCS is installed in a subfolder than your directory structure would be
/subfolder/modules/addons

4. Once you have done this. Goto your admin area in WHMCS, and Navigate to 
-> Setup -> Addon Modules
Scroll down until you see "Linode DNS Manager" Click on Activate

5. Once it is activated click on Configure. Fill in the information request. You must enter your linode API key, and your SOA Email address. The Enable SSL and Homepage Display options are optional. (Make sure to click the checkbox for Full Administrator.

6. Within WHMCS Admin navigate to: -> Addons -> Linode DNS Manager

7. This is the admin interface where you can make some changes. To get started you will likely want to click the Servers button. Here will have a list of your servers. Simply select which servers you want to use this addon module on and click save.

8. Once this is done you can now also link your existing hosting accounts within WHMCS to your domains zone files at linode by pushing the link accounts button. At this point your clients will have access to the domains assoicated with they're accounts within WHMCS. 

9. If your clients have add-on domains or parked domains that already exist. You can give them access to those zones as well by pushing the Client Access Domains button. Select the client and the domain and Click Add. You can also remove access rights from this screen as well.

10. Lastly. We provide a basic DNS skeleton automatically for each server within your WHMCS install. You can customize these skeletons if you like. This skeleton is used when a client either add's a zone from the client area. Or a new or existing client orders hosting services. These DNS Records will be automatically added with the correct ip address and other relivant information.
