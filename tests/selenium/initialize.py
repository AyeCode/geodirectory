import os
import sys

try:
    github_user = sys.argv[1]
except IndexError:
    print "github username not provided"
    print "Usage: python initialize.py [github username] [bitbucket username]"

try:
    bitbucket_user = sys.argv[2]
except IndexError:
    print "bitbucket username not provided"
    print "Usage: python initialize.py [github username] [bitbucket username]"

plugin_dir = os.path.dirname(os.path.abspath(__file__))

# plugin_dir = '/Users/giri/Sites/wordpress/wp-content/plugins'
# for item in os.listdir(plugin_dir):
#     print item

if not plugin_dir.endswith("wp-content/plugins"):
	sys.exit("Current folder doesn't look like a plugins folder. Make sure you are in wp-content/plugins folder before running the script. Stopping the script....")


plugins = {
	'geodirectory' : "https://github.com/%s/geodirectory.git" % github_user,
	'geodir_advance_search_filters' : "https://%s@bitbucket.org/%s/geodir_advance_search_filters.git" % (bitbucket_user, bitbucket_user),
	'geodir_affiliate'  : "https://%s@bitbucket.org/%s/geodir_affiliate.git" % (bitbucket_user, bitbucket_user),
	'geodir_ajax_duplicate_alert'  : "https://%s@bitbucket.org/%s/geodir_ajax_duplicate_alert.git" % (bitbucket_user, bitbucket_user),
	'geodir_buddypress'  : "https://%s@bitbucket.org/%s/geodir_buddypress.git" % (bitbucket_user, bitbucket_user),
	'geodir_claim_listing'  : "https://%s@bitbucket.org/%s/geodir_claim_listing.git" % (bitbucket_user, bitbucket_user),
	'geodir_custom_posts'  : "https://%s@bitbucket.org/%s/geodir_custom_posts.git" % (bitbucket_user, bitbucket_user),
	'geodir_event_manager'  : "https://%s@bitbucket.org/%s/geodir_event_manager.git" % (bitbucket_user, bitbucket_user),
	'geodir_gd_booster'  : "https://%s@bitbucket.org/%s/geodir_gd_booster.git" % (bitbucket_user, bitbucket_user),
	'geodir_location_manager'  : "https://%s@bitbucket.org/%s/geodir_location_manager.git" % (bitbucket_user, bitbucket_user),
	'geodir_marker_cluster'  : "https://%s@bitbucket.org/%s/geodir_marker_cluster.git" % (bitbucket_user, bitbucket_user),
	'geodir_payment_manager'  : "https://%s@bitbucket.org/%s/geodir_payment_manager.git" % (bitbucket_user, bitbucket_user),
	'geodir_recaptcha'  : "https://%s@bitbucket.org/%s/geodir_recaptcha.git" % (bitbucket_user, bitbucket_user),
	'geodir_review_rating_manager'  : "https://%s@bitbucket.org/%s/geodir_review_rating_manager.git" % (bitbucket_user, bitbucket_user),
	'geodir_social_importer'  : "https://%s@bitbucket.org/%s/geodir_social_importer.git" % (bitbucket_user, bitbucket_user),
	'geodir_stripe_payment_manager'  : "https://%s@bitbucket.org/%s/geodir_stripe_payment_manager.git" % (bitbucket_user, bitbucket_user),
	'bp-default-data'  : "https://github.com/slaFFik/BP-Default-Data.git",
	'buddypress'  : "https://github.com/buddypress/BuddyPress.git",
	'buddypress-compliments'  : "https://github.com/%s/buddypress-compliments.git" % github_user,
	'wordpress-database-reset'  : "https://github.com/chrisberthe/wordpress-database-reset.git",
	}

for plugin, url in plugins.iteritems():
	if not os.path.exists("%s/%s" % (plugin_dir, plugin)):
		print "%s plugin not installed. Installing plugin" % plugin
		os.system("git clone %s" % url)




