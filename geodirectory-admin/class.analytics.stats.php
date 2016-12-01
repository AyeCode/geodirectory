<?php


/**
 * Handles interactions with Google Analytics' Stat API
 *
 **/
class GDGoogleAnalyticsStats
{

	var $client = false;
	var $accountId;
	var $baseFeed = 'https://www.googleapis.com/analytics/v3';
	var $token = false;


	/**
	 * Constructor
	 *
	 * @param token - a one-time use token to be exchanged for a real token
	 **/
	public function __construct()
	{

//			# Include SimplePie if it doesn't exist
//			if ( !class_exists('SimplePie') ) {
//				require_once (ABSPATH . WPINC . '/class-feed.php');
//			}

			// Include the Google Service API
			include_once('google-api-php-client/src/Google/autoload.php');

            $this->client = new Google_Client();
            $this->client->setApprovalPrompt("force");
            $this->client->setAccessType('offline');
            $this->client->setClientId(GEODIR_GA_CLIENTID);
            $this->client->setClientSecret(GEODIR_GA_CLIENTSECRET);
            $this->client->setRedirectUri(GEODIR_GA_REDIRECT);
			
            $this->client->setScopes(array("https://www.googleapis.com/auth/analytics"));

            try {
                    $this->analytics = new Google_Service_Analytics($this->client);
                }
            catch (Google_ServiceException $e)
                {
                    print '(cas:48) There was an Analytics API service error ' . $e->getCode() . ':' . $e->getMessage();
					return false;
                }
	}

	function checkLogin()
	{
            $ga_google_authtoken = get_option('geodir_ga_auth_token');

            if (!empty($ga_google_authtoken))
            {
				try
                {
                    $this->client->setAccessToken($ga_google_authtoken);
				}
				catch( Google_AuthException $e )
                {
                    print '(cas:72) GeoDirectory was unable to authenticate you with
                            Google using the Auth Token you pasted into the input box on the previous step. <br><br>
                            This could mean either you pasted the token wrong, or the time/date on your server is wrong,
                            or an SSL issue preventing Google from Authenticating. <br><br>
                            <br><br><strong>Tech Info </strong> ' . $e->getCode() . ':' . $e->getMessage();

                    return false;
                }
            }
            else
            {
                $authCode = get_option('geodir_ga_auth_code');

                if (empty($authCode)) return false;

                try
                {
                    $accessToken = $this->client->authenticate($authCode);
                }
                catch( Exception $e )
                {
                    print '(cas:72) GeoDirectory was unable to authenticate you with
                            Google using the Auth Token you pasted into the input box on the previous step. <br><br>
                            This could mean either you pasted the token wrong, or the time/date on your server is wrong,
                            or an SSL issue preventing Google from Authenticating. <br><br>
                            <br><br><strong>Tech Info </strong> ' . $e->getCode() . ':' . $e->getMessage();

                    return false;
                }

                if($accessToken)
                {
                    $this->client->setAccessToken($accessToken);
                    update_option('geodir_ga_auth_token', $accessToken);
                }
                else
                {
                    return false;
                }
            }

            $this->token =  $this->client->getAccessToken();
            return true;
	}

	function deauthorize()
	{
            update_option('geodir_ga_auth_code', '');
            update_option('geodir_ga_auth_token', '');
	}

	function getSingleProfile()
	{
            $webproperty_id = get_option('geodir_ga_account_id');
            list($pre, $account_id, $post) = explode('-',$webproperty_id);

            if (empty($webproperty_id)) return false;

            try {
                $profiles = $this->analytics->management_profiles->listManagementProfiles($account_id, $webproperty_id);
            }
            catch (Google_ServiceException $e)
            {
                print 'There was an Analytics API service error ' . $e->getCode() . ': ' . $e->getMessage();
                return false;
            }

            $profile_id = $profiles->items[0]->id;
            if (empty($profile_id)) return false;

            $account_array = array();
            array_push($account_array, array('id'=>$profile_id, 'ga:webPropertyId'=>$webproperty_id));
            return $account_array;
	}

        function getAllProfiles()
        {
            $profile_array = array();
            
            try {
                    $profiles = $this->analytics->management_webproperties->listManagementWebproperties('~all');
                }
                catch (Google_ServiceException $e)
                {
                    print 'There was an Analytics API service error ' . $e->getCode() . ': ' . $e->getMessage();
                }


            if( !empty( $profiles->items ) )
            {
                foreach( $profiles->items as $profile )
                {
                    $profile_array[ $profile->id ] = str_replace('http://','',$profile->name );
                }
            }

            return $profile_array;
        }

	function getAnalyticsAccounts()
	{
		$analytics = new Google_Service_Analytics($this->client);
		$accounts = $analytics->management_accounts->listManagementAccounts();
		$account_array = array();

		$items = $accounts->getItems();

		if (count($items) > 0) {
			foreach ($items as $key => $item)
			{
				$account_id = $item->getId();

				$webproperties = $analytics->management_webproperties->listManagementWebproperties($account_id);

				if (!empty($webproperties))
				{
					foreach ($webproperties->getItems() as $webp_key => $webp_item) {
						$profiles = $analytics->management_profiles->listManagementProfiles($account_id, $webp_item->id);

						$profile_id = $profiles->items[0]->id;
						array_push($account_array, array('id'=>$profile_id, 'ga:webPropertyId'=>$webp_item->id));
					}
				}
			}

			return $account_array;
		}
		return false;

	}



	/**
	 * Sets the account id to use for queries
	 *
	 * @param id - the account id
	 **/
	function setAccount($id)
	{
		$this->accountId = $id;
	}


	/**
	 * Get a specific data metrics
	 *
	 * @param metrics - the metrics to get
	 * @param startDate - the start date to get
	 * @param endDate - the end date to get
	 * @param dimensions - the dimensions to grab
	 * @param sort - the properties to sort on
	 * @param filter - the property to filter on
	 * @param limit - the number of items to get
	 * @param realtime - if the realtime api should be used
	 * @return the specific metrics in array form
	 **/
	function getMetrics($metric, $startDate, $endDate, $dimensions = false, $sort = false, $filter = false, $limit = false, $realtime = false)
	{
		$analytics = new Google_Service_Analytics($this->client);

		$params = array();

		if ($dimensions)
		{
			$params['dimensions'] = $dimensions;
		}
		if ($sort)
		{
			$params['sort'] = $sort;
		}
		if ($filter)
		{
			$params['filters'] = $filter;
		}
		if ($limit)
		{
			$params['max-results'] = $limit;
		}
           
           // Just incase, the ga: is still used in the account id, strip it out to prevent it breaking
           $filtered_id = str_replace( 'ga:', '', $this->accountId );
           
           if(!$filtered_id){
                echo 'Error - Account ID is blank';
                return false;
           }

		if($realtime){
			return $analytics->data_realtime->get(
				'ga:'.$filtered_id,
				$metric,
				$params
			);
		}else{
			return $analytics->data_ga->get(
				'ga:'.$filtered_id,
				$startDate,
				$endDate,
				$metric,
				$params
			);
		}

	}





	/**
	 * Checks the date against Jan. 1 2005 because GA API only works until that date
	 *
	 * @param date - the date to compare
	 * @return the correct date
	 **/
	function verifyStartDate($date)
	{
		if ( strtotime($date) > strtotime('2005-01-01') )
			return $date;
		else
			return '2005-01-01';
	}

} // END class	