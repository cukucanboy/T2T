<?php
class Activity_lib {
/**
* Protected variables
*/
  protected $ci = NULL; //codeigniter instance
  protected $db; //database instatnce instance
  public $appSettings;
  public $activityid;
  public $title;
  public $slug;
  public $bookingSlug;
  public $stars;
  public $desc;
  public $policy;
  public $basicprice;
  public $discountprice;
  protected $lang;
  public $currencysign;
  public $currencycode;
  public $location;
  public $latitude;
  public $longitude;
  public $isfeatured;
  public $thumbnail;
  public $inclusions;
  public $exclusions;
  public $adultStatus;
  public $childStatus;
  public $infantStatus;
  public $activityNights;
  public $activityDays;
  public $activityType;
  public $adults;
  public $child;
  public $infants;
  public $selectedLocation;
  public $selectedActivityType;
  public $date;
  public $adultPrice;
  public $childPrice;
  public $infantPrice;
  public $urlVars;
  public $error;
  public $errorCode;
  public $tax_type;
  public $tax_value;
  public $deposit = 0;
  public $taxamount;
  public $guestCount;
  public $createdAt;
  public $langdef;
  function __construct() {

//get the CI instance
    $this->ci = & get_instance();
    $this->db = $this->ci->db;
    $this->appSettings = $this->ci->Settings_model->get_settings_data();
    $lang = $this->ci->session->userdata('set_lang');
    $defaultlang = pt_get_default_language();
    $this->ci->load->model('Activity/Activity_model');
    $this->ci->load->helper('Activity/activity_front');
    if (empty($lang)) {
      $this->lang = $defaultlang;
    }
    else {
      $this->lang = $lang;
    }
    $this->error = false;
    $this->errorCode = "";
    $this->date = $this->ci->input->get('date');
    $typeid = $this->ci->input->get('type');
    $this->selectedActivityType = $this->selectedActivityType($typeid);
    $selectedAdults = $this->ci->input->get('adults');
    $selecteChildren = $this->ci->input->get('child');
    $selectedInfants = $this->ci->input->get('infant');
    $loc = $this->ci->input->get('location');
    if (!empty($selectedAdults)) {
      $this->adults = $selectedAdults;
    }
    else {
      $this->adults = PT_DEFAULT_ADULTS_COUNT;
    }
    if (!empty($selecteChildren)) {
      $this->child = $selecteChildren;
    }
    else {
      $this->child = 0;
    }
    if (!empty($selectedInfants)) {
      $this->infants = $selectedInfants;
    }
    else {
      $this->infants = 0;
    }
    if (!empty($loc)) {
      $this->selectedLocation = $loc;
    }
    else {
      $this->selectedLocation = "";
    }
    if (empty($this->date)) {
      $this->date = date($this->appSettings[0]->date_f, strtotime('+' . CHECKIN_SPAN . ' day', time()));
    }
    $this->guestCount = $this->adults + $this->child + $this->infants;
    $getVariables = $this->ci->input->get();
    if (!empty($getVariables)) {
      $this->urlVars = "?date=" . $this->date . "&adults=" . $this->adults;
    }
    else {
      $this->urlVars = "";
    }
    $this->langdef = DEFLANG;
  }
  function set_activityid($activitylug) {
    $this->db->select('activity_id');
    $this->db->where('activity_slug', $activitylug);
    $r = $this->db->get('pt_activity')->result();
    $this->activityid = $r[0]->activity_id;
  }
  function set_lang($lang) {
    if (empty($lang)) {
      $defaultlang = pt_get_default_language();
      $this->lang = $defaultlang;
    }
    else {
      $this->lang = $lang;
    }
  }
//set activity id by id
  function set_id($id, $currsign = null, $currcode = null) {
    $this->activityid = $id;
    $this->currencysign = $currsign;
    $this->currencycode = $currcode;
  }
  function get_id() {
    return $this->activityid;
  }
  function settings() {
    return $this->ci->Settings_model->get_front_settings('activity');
  }
  function wishListInfo($id) {
    $this->activity_short_details($id);
    $title = $this->title;
    $slug = base_url() . 'activity/' . $this->slug;
    $thumbnail = $this->thumbnail;
    $location = $this->location;
    $stars = pt_create_stars($this->stars);
    $res = array("title" => $title, "slug" => $slug, "thumbnail" => $thumbnail, "location" => $location->city, "stars" => $stars,);
    return $res;
  }
  function selectedActivityType($id) {
    $option = "";
    if (!empty($id)) {
      $res = $this->activityTypeSettings($id);
      if (!empty($res->name)) {
        $option = "<option value=" . $res->id . " selected >" . $res->name . "</option>";
      }
    }
    return $option;
  }
  function show_activity($offset = null) {
    $totalSegments = $this->ci->uri->total_segments();
    $data = array();
    $settings = $this->settings();
    $perpage = $settings[0]->front_listings;
    $sortby = $this->ci->input->get('sortby');
    if (!empty($sortby)) {
      $orderby = $sortby;
    }
    else {
      $orderby = $settings[0]->front_listings_order;
    }
    $priceRange = $this->priceRange($this->ci->input->get('price'));
    $rh = $this->ci->Activity_model->list_activity_front($priceRange);
    $activity = $this->ci->Activity_model->list_activity_front($priceRange, $perpage, $offset, $orderby);
    $data['all_activity'] = $this->getResultObject($activity['all']);
    $data['paginationinfo'] = array('base' => 'activity/listing', 'totalrows' => $rh['rows'], 'perpage' => $perpage,'urisegment' => $totalSegments);
    return $data;
  }
  function showActivityByLocation($locs, $offset = null) {
    $data = array();
    $settings = $this->settings();
    $perpage = $settings[0]->front_listings;
    $sortby = $this->ci->input->get('sortby');
    if (!empty($sortby)) {
      $orderby = $sortby;
    }
    else {
      $orderby = $settings[0]->front_listings_order;
    }
    $priceRange = $this->priceRange($this->ci->input->get('price'));
    $rh = $this->ci->Activity_model->showActivityByLocation($locs->locations, $priceRange);
    $activity = $this->ci->Activity_model->showActivityByLocation($locs->locations, $priceRange, $perpage, $offset, $orderby);
    $data['all_activity'] = $this->getResultObject($activity['all']);
    $data['paginationinfo'] = array('base' => 'activity/' . $locs->urlBase, 'totalrows' => $rh['rows'], 'perpage' => $perpage, 'urisegment' => $locs->uriSegment);
    return $data;
  }
  function search_activity($location, $offset = null) {
    $data = array();
    $settings = $this->settings();
    $perpage = $settings[0]->front_search;
    $orderby = $settings[0]->front_search_order;
    $totalSegments = $this->ci->uri->total_segments();
    if ($totalSegments < 5) {
      $location = "";
      $segments = "";
      $urisegment = 3;
    }
    else {
      $segments = '/' . $this->ci->uri->segment(3) . '/' . $this->ci->uri->segment(4) . '/' . $this->ci->uri->segment(5);
      $urisegment = 6;
    }
    $priceRange = $this->priceRange($this->ci->input->get('price'));
    $rh = $this->ci->Activity_model->search_activity_front($location, $priceRange);
    $activity = $this->ci->Activity_model->search_activity_front($location, $priceRange, $perpage, $offset, $orderby);
    $data['all_activity'] = $this->getResultObject($activity['all']);
    $data['paginationinfo'] = array('base' => 'activity/search' . $segments, 'totalrows' => $rh['rows'], 'perpage' => $perpage, 'urisegment' => $urisegment);
    return $data;
  }
  function activity_details($activityid = null, $date = null) {
    if (empty($activityid)) {
      $activityid = $this->activityid;
    }
    else {
      $activityid = $activityid;
    }
    $this->ci->load->library('currconverter');
    $curr = $this->ci->currconverter;
    if (!empty($date)) {
      $this->date = $date;
    }
    $this->db->where('activity_id', $activityid);
    $details = $this->db->get('pt_activity')->result();
    $title = $this->get_title($details[0]->activity_title, $details[0]->activity_id);
    $stars = $details[0]->activity_stars;
    $desc = $this->get_description($details[0]->activity_desc, $details[0]->activity_id);
    $policy = $this->get_policy($details[0]->activity_privacy, $details[0]->activity_id);
    $locationInfoUrl = pt_LocationsInfo($details[0]->activity_location);
    $countryName = url_title($locationInfoUrl->country, 'dash', true);
    $cityName = url_title($locationInfoUrl->city, 'dash', true);
    $slug = $countryName . '/' . $cityName . '/' . $details[0]->activity_slug . $this->urlVars;
    $bookingSlug = $details[0]->activity_slug . $this->urlVars;
    $keywords = $this->get_keywords($details[0]->activity_meta_keywords, $details[0]->activity_id);
    $metadesc = $this->get_metaDesc($details[0]->activity_meta_desc, $details[0]->activity_id);
    $activityDays = $details[0]->activity_days;
    $activityNights = $details[0]->activity_nights;
    if (!empty($details[0]->activity_amenities)) {
      $activityAmenities = explode(",", $details[0]->activity_amenities);
      foreach ($activityAmenities as $tm) {
        $amts[] = $this->activityTypeSettings($tm);
      }
    }
    else {
      $amts = array();
    }
    $inclusions = $amts;
    if (!empty($details[0]->activity_exclusions)) {
      $activityExclusions = explode(",", $details[0]->activity_exclusions);
      foreach ($activityExclusions as $exc) {
        $excs[] = $this->activityTypeSettings($exc);
      }
    }
    else {
      $excs = array();
    }
    $exclusions = $excs;
    if (!empty($details[0]->activity_payment_opt)) {
      $activityPaymentOpts = explode(",", $details[0]->activity_payment_opt);
      foreach ($activityPaymentOpts as $p) {
        $payopts[] = $this->activityTypeSettings($p);
      }
    }
    else {
      $payopts = array();
    }
    $paymentOptions = $payopts;
    if (!empty($details[0]->activity_related)) {
      $ractivity = explode(",", $details[0]->activity_related);
    }
    else {
      $ractivity = "";
    }
    $relatedActivity = $this->getRelatedActivity($ractivity);
    $thumbnail = PT_ACTIVITY_SLIDER_THUMB . $details[0]->thumbnail_image;
    $city = pt_LocationsInfo($details[0]->activity_location, $this->lang);
    $location = $city->city; // $details[0]->activity_location;
//	$isfeatured = $this->is_featured();
    $website = $details[0]->activity_website;
    $phone = $details[0]->activity_phone;
    $email = $details[0]->activity_email;
    $taxcom = $this->activity_tax_commision();
    $comm_type = $taxcom['commtype'];
    $comm_value = $taxcom['commval'];
    $tax_type = $taxcom['taxtype'];
    $tax_value = $taxcom['taxval'];
    $latitude = $details[0]->activity_latitude;
    $longitude = $details[0]->activity_longitude;
    $totalAdutlsPrice = $details[0]->activity_adult_price * $this->adults;
    $totalChildPrice = $details[0]->activity_child_price * $this->child;
    $totalInfantsPrice = $details[0]->activity_infant_price * $this->infants;
    $adultPrice = $curr->convertPrice($totalAdutlsPrice);
    $childPrice = $curr->convertPrice($totalChildPrice);
    $infantPrice = $curr->convertPrice($totalInfantsPrice);
    $perAdultPrice = $curr->convertPrice($details[0]->activity_adult_price);
    $perChildPrice = $curr->convertPrice($details[0]->activity_child_price);
    $perInfantPrice = $curr->convertPrice($details[0]->activity_infant_price);
    $maxAdults = $details[0]->activity_max_adults;
    $maxChild = $details[0]->activity_max_child;
    $maxInfant = $details[0]->activity_max_infant;
    $this->checkErrors($maxAdults, $maxChild, $maxInfant);
    $adultStatus = $details[0]->adult_status;
    $childStatus = $details[0]->child_status;
    $infantStatus = $details[0]->infant_status;
    $sliderImages = $this->activityImages($details[0]->activity_id);
    $totalCost = $totalAdutlsPrice + $totalChildPrice + $totalInfantsPrice;
    $taxcom = $this->activity_tax_commision($details[0]->activity_id);
    $this->comm_type = $taxcom['commtype'];
    $this->comm_value = $taxcom['commval'];
    $this->tax_type = $taxcom['taxtype'];
    $this->tax_value = $taxcom['taxval'];
    $this->setDeposit($curr->convertPriceFloat($totalCost, 2));
    $depositAmount = $this->deposit;
    $detailResults = (object) array('id' => $details[0]->activity_id, 'title' => $title, 'slug' => $slug, 'bookingSlug' => $bookingSlug, 'thumbnail' => $thumbnail, 'stars' => pt_create_stars($stars), 'starsCount' => $stars, 'location' => $location, 'desc' => $desc, 'inclusions' => $inclusions, 'exclusions' => $exclusions, 'latitude' => $latitude, 'longitude' => $longitude, 'sliderImages' => $sliderImages, 'relatedItems' => $relatedActivity, 'paymentOptions' => $paymentOptions, 'metadesc' => $metadesc, 'keywords' => $keywords, 'policy' => $policy, 'website' => $website, 'email' => $email, 'phone' => $phone, 'maxAdults' => $maxAdults, 'maxChild' => $maxChild, 'maxInfant' => $maxInfant, 'adultStatus' => $adultStatus, 'childStatus' => $childStatus, 'infantStatus' => $infantStatus, 'adultPrice' => $adultPrice, 'childPrice' => $childPrice, 'infantPrice' => $infantPrice, 'perAdultPrice' => $perAdultPrice, 'perChildPrice' => $perChildPrice, 'perInfantPrice' => $perInfantPrice, 'currCode' => $curr->code, 'currSymbol' => $curr->symbol, 'date' => $this->date, 'totalCost' => $curr->convertPrice($totalCost), 'comType' => $comm_type, 'comValue' => $comm_value, 'taxType' => $tax_type, 'taxValue' => $tax_value, 'activityDays' => $activityDays, 'activityNights' => $activityNights, 'totalDeposit' => $depositAmount, 'mapAddress' => $details[0]->activity_mapaddress);
    return $detailResults;
  }
  function activity_short_details($activityid) {
    if (empty($activityid)) {
      $activityid = $this->activityid;
    }
    $this->db->select('activity_title,activity_stars,activity_slug,activity_desc,activity_privacy,activity_max_adults,activity_max_child,
   activity_max_infant,activity_basic_price,activity_basic_discount,activity_adult_price,activity_child_price,activity_infant_price,activity_amenities,activity_exclusions,activity_days,activity_nights,thumbnail_image,activity_location,activity_latitude,activity_longitude,activity_type,activity_created_at');
    $this->db->where('activity_id', $activityid);
    $details = $this->db->get('pt_activity')->result();
    $this->stars = $details[0]->activity_stars;
    $this->title = $this->get_title($details[0]->activity_title);
    $this->desc = $this->get_description($details[0]->activity_desc);
    $this->policy = $this->get_policy($details[0]->activity_privacy);
    $this->activityDays = $details[0]->activity_days;
    $this->activityNights = $details[0]->activity_nights;
    $this->activityNights = $details[0]->activity_nights;
    $this->createdAt = $details[0]->activity_created_at;
    $maxAdults = $details[0]->activity_max_adults;
    $maxChild = $details[0]->activity_max_child;
    $maxInfant = $details[0]->activity_max_infant;
    $this->checkErrors($maxAdults, $maxChild, $maxInfant);
//get country and city name for url slug
    $locationInfoUrl = pt_LocationsInfo($details[0]->activity_location);
    $countryName = url_title($locationInfoUrl->country, 'dash', true);
    $cityName = url_title($locationInfoUrl->city, 'dash', true);
    $this->slug = $countryName . '/' . $cityName . '/' . $details[0]->activity_slug . $this->urlVars;
    $this->bookingSlug = $details[0]->activity_slug . $this->urlVars;
    $city = pt_LocationsInfo($details[0]->activity_location, $this->lang);
    $this->location = $city->city;
//$details[0]->activity_location;
    $this->latitude = $details[0]->activity_latitude;
    $this->longitude = $details[0]->activity_longitude;
    $this->thumbnail = PT_ACTIVITY_SLIDER_THUMB . $details[0]->thumbnail_image;
    $type = $this->activityTypeSettings($details[0]->activity_type);
    $this->activityType = $type->name;
    $taxcom = $this->activity_tax_commision();
    $this->comm_type = $taxcom['commtype'];
    $this->comm_value = $taxcom['commval'];
    $this->tax_type = $taxcom['taxtype'];
    $this->tax_value = $taxcom['taxval'];
    $this->adultPrice = $details[0]->activity_adult_price;
    $this->childPrice = $details[0]->activity_child_price;
    $this->infantPrice = $details[0]->activity_infant_price;
    $this->isfeatured = $this->is_featured();
    return $details;
  }
  function activity_tax_commision($activityid = null) {
    if (empty($activityid)) {
      $activityid = $this->activityid;
    }
    $res = array();
    $this->db->select('activity_comm_fixed,activity_comm_percentage,activity_tax_fixed,activity_tax_percentage');
    $this->db->where('activity_id', $activityid);
    $result = $this->db->get('pt_activity')->result();
    $commfixed = $result[0]->activity_comm_fixed;
    $commper = $result[0]->activity_comm_percentage;
    $taxfixed = $result[0]->activity_tax_fixed;
    $taxper = $result[0]->activity_tax_percentage;
    $res['commtype'] = "percentage";
    $res['commval'] = $commper;
    $res['taxtype'] = "percentage";
    $res['taxval'] = $taxper;
    if ($commfixed > 0) {
      $res['commtype'] = "fixed";
      $res['commval'] = $commfixed;
    }
    if ($taxfixed > 0) {
      $res['taxtype'] = "fixed";
      $res['taxval'] = $taxfixed;
    }
    return $res;
  }
// get activity images
  function activityImages($activityid) {
    if (empty($activityid)) {
      $activityid = $this->activityid;
    }
    $this->db->where('timg_activity_id', $activityid);
    $this->db->where('timg_approved', '1');
    $this->db->order_by('timg_order', 'asc');
    $res = $this->db->get('pt_activity_images')->result();
    if (empty($res)) {
      $result[] = array("fullImage" => PT_ACTIVITY_SLIDER_THUMB . PT_BLANK_IMG, "thumbImage" => PT_ACTIVITY_SLIDER_THUMB . PT_BLANK_IMG);
    }
    else {
      foreach ($res as $r) {
        $result[] = array("fullImage" => PT_ACTIVITY_SLIDER . $r->timg_image, "thumbImage" => PT_ACTIVITY_SLIDER_THUMB . $r->timg_image);
      }
    }
    return $result;
  }
  function getFeaturedActivity() {
    $activity = $this->featured_activity_list();
    $result = $this->getResultObject($activity);
    return $result;
  }
  function getLocationBasedFeaturedActivity($loc) {
    $settings = $this->settings();
    $limit = $settings[0]->front_homepage;
    $this->db->select('activity_id');
    $this->db->where('activity_location', $loc);
    $this->db->where('activity_status', 'Yes');
    $activityList = $this->db->get('pt_activity')->result();
    $activity = array();
    foreach ($activityList as $t) {
      $isFeatured = $this->is_featured($t->activity_id);
      if ($isFeatured) {
        $activity[] = (object) array('activity_id' => $t->activity_id);
      }
    }
    $activity = array_slice($activity, 0, $limit);
    $result = $this->getResultObject($activity);
    return $result;
  }
  function getTopRatedActivity() {
    $activity = $this->ci->Activity_model->popular_activity_front();
    $result = $this->getResultObject($activity);
    return $result;
  }
  function getRelatedActivity($activity) {
    $resultactivity = array();
    $result = array();
    $settings = $this->settings();
    $limit = $settings[0]->front_related;
    $count = 0;
    if (!empty($activity)) {
      foreach ($activity as $t) {
        $count++;
        if($count <= $limit){
          $resultactivity[] = (object) array('activity_id' => $t);
        }

      }
    }
    $result = $this->getLimitedResultObject($resultactivity);
    return $result;
  }
// Get Activity updated Price on changing adults, child and infant count.
  function updatedPrice($activityid, $adults = 1, $child = 0, $infant = 0) {
    $this->ci->load->library('currconverter');
    $curr = $this->ci->currconverter;
    $this->db->select('activity_adult_price,activity_child_price,activity_infant_price');
    $this->db->where('activity_id', $activityid);
    $details = $this->db->get('pt_activity')->result();
    $totalAdutlsPrice = $details[0]->activity_adult_price * $adults;
    $totalChildPrice = $details[0]->activity_child_price * $child;
    $totalInfantsPrice = $details[0]->activity_infant_price * $infant;
    $adultPrice = $curr->convertPrice($totalAdutlsPrice);
    $childPrice = $curr->convertPrice($totalChildPrice);
    $infantPrice = $curr->convertPrice($totalInfantsPrice);
    $totalCost = $totalAdutlsPrice + $totalChildPrice + $totalInfantsPrice;
    $taxcom = $this->activity_tax_commision($activityid);
    $this->comm_type = $taxcom['commtype'];
    $this->comm_value = $taxcom['commval'];
    $this->tax_type = $taxcom['taxtype'];
    $this->tax_value = $taxcom['taxval'];
    $this->setDeposit($totalCost);
    $depositAmount = $this->deposit;
    if (!empty($curr->symbol)) {
      $currSymbol = $curr->symbol;
    }
    else {
      $currSymbol = "";
    }
    $detailResults = array('id' => $activityid, 'adultPrice' => $adultPrice, 'childPrice' => $childPrice, 'infantPrice' => $infantPrice, 'currCode' => $curr->code, 'currSymbol' => $currSymbol, 'totalDeposit' => $curr->convertPrice($depositAmount), 'totalCost' => $curr->convertPrice($totalCost));
    return json_encode($detailResults);
  }
  function get_thumbnail() {
    $res = $this->ci->Activity_model->default_activity_img($this->activityid);
    if (!empty($res)) {
      return PT_ACTIVITY_SLIDER_THUMB . $res;
    }
    else {
      return PT_BLANK;
    }
  }
  function get_title($deftitle, $activityid) {
    if (empty($activityid)) {
      $activityid = $this->activityid;
    }
    if ($this->lang == $this->langdef) {
      $title = $deftitle;
    }
    else {
      $this->db->where('item_id', $activityid);
      $this->db->where('trans_lang', $this->lang);
      $res = $this->db->get('pt_activity_translation')->result();
      $title = $res[0]->trans_title;
      if (empty($title)) {
        $title = $deftitle;
      }
    }
    return $title;
  }
  function get_description($defdesc, $activityid) {
    if (empty($activityid)) {
      $activityid = $this->activityid;
    }
    if ($this->lang == $this->langdef) {
      $desc = $defdesc;
    }
    else {
      $this->db->where('item_id', $activityid);
      $this->db->where('trans_lang', $this->lang);
      $res = $this->db->get('pt_activity_translation')->result();
      $desc = $res[0]->trans_desc;
      if (empty($desc)) {
        $desc = $defdesc;
      }
    }
    return $desc;
  }
  function get_policy($defpolicy, $activityid) {
    if (empty($activityid)) {
      $activityid = $this->activityid;
    }
    if ($this->lang == $this->langdef) {
      $policy = $defpolicy;
    }
    else {
      $this->db->where('item_id', $activityid);
      $this->db->where('trans_lang', $this->lang);
      $res = $this->db->get('pt_activity_translation')->result();
      $policy = $res[0]->trans_policy;
      if (empty($policy)) {
        $policy = $defpolicy;
      }
    }
    return $policy;
  }
  function get_keywords($defkeywords, $activityid = null) {
    if (empty($activityid)) {
      $activityid = $this->activityid;
    }
    if ($this->lang == $this->langdef) {
      $keywords = $defkeywords;
    }
    else {
      $this->db->where('item_id', $activityid);
      $this->db->where('trans_lang', $this->lang);
      $res = $this->db->get('pt_activity_translation')->result();
      $keywords = $res[0]->metakeywords;
      if (empty($keywords)) {
        $keywords = $defkeywords;
      }
    }
    return $keywords;
  }
  function get_metaDesc($defmeta, $activityid = null) {
    if (empty($activityid)) {
      $activityid = $this->activityid;
    }
    if ($this->lang == $this->langdef) {
      $meta = $defmeta;
    }
    else {
      $this->db->where('item_id', $activityid);
      $this->db->where('trans_lang', $this->lang);
      $res = $this->db->get('pt_activity_translation')->result();
      $meta = $res[0]->metadesc;
      if (empty($meta)) {
        $meta = $defmeta;
      }
    }
    return $meta;
  }
  function activityExtras($activityid = null) {
    if (empty($activityid)) {
      $activityid = $this->activityid;
    }
    $today = time();
    $result = array();
//	$this->db->where('extras_from  <=', $today);
//	$this->db->where('extras_to >=', $today);
    $this->db->where('extras_module', 'activity');
//  $this->db->or_where('extras_forever','forever');
    $this->db->order_by('extras_id', 'desc');
    $this->db->like('extras_for', $activityid, 'both');
    $this->db->having('extras_status', 'Yes');
    $ext = $this->db->get('pt_extras')->result();
    $this->ci->load->library('currconverter');
    $curr = $this->ci->currconverter;
    if (!empty($ext)) {
      foreach ($ext as $e) {
        $trans = $this->extrasTranslation($e->extras_id, $e->extras_title, $e->extras_desc);
        $price = $curr->convertPrice($e->extras_basic_price);
        $result[] = (object) array("id" => $e->extras_id, "extraTitle" => $trans['title'], "extraDesc" => $trans['desc'], 'extraPrice' => $price, 'thumbnail' => PT_ACTIVITY_EXTRAS_IMAGES . $e->extras_image);
      }
    }
    return $result;
  }
  function getLocationsList() {
    $resultLocations = array();
    $this->db->select('location_id');
    $this->db->group_by('location_id');
    $locations = $this->db->get('pt_activity_locations')->result();
    foreach ($locations as $loc) {
      $locInfo = pt_LocationsInfo($loc->location_id, $this->lang);
      if (!empty($locInfo->city)) {
        $resultLocations[] = (object) array('id' => $locInfo->id, 'name' => $locInfo->city);
      }
    }
    return $resultLocations;
  }
  function extras_translation($id) {
    $language = $this->lang;
    $result = array();
    $this->db->select('extras_title,extras_desc');
    $this->db->where('extras_id', $id);
    $re = $this->db->get('pt_extras')->result();
    if ($language == $this->langdef) {
      $result['title'] = $re[0]->extras_title;
      $result['desc'] = $re[0]->extras_desc;
    }
    else {
      $this->db->select('trans_title,trans_desc');
      $this->db->where('trans_extras_id', $id);
      $this->db->where('trans_lang', $language);
      $r = $this->db->get('pt_extras_translation')->result();
      if (empty($r[0]->trans_title)) {
        $result['title'] = $re[0]->extras_title;
      }
      else {
        $result['title'] = $r[0]->trans_title;
      }
      if (empty($r[0]->trans_desc)) {
        $result['desc'] = $re[0]->extras_desc;
      }
      else {
        $result['desc'] = $r[0]->trans_desc;
      }
    }
    return $result;
  }
// activity Reviews
  function activity_reviews($activityid) {
    if (empty($activityid)) {
      $activityid = $this->activityid;
    }
    $this->db->where('review_status', 'Yes');
    $this->db->where('review_module', 'activity');
    $this->db->where('review_itemid', $activityid);
    $this->db->order_by('review_id', 'desc');
    return $this->db->get('pt_reviews')->result();
  }
// activity Reviews for API
  function activity_reviews_for_api($activityid) {
    if (empty($activityid)) {
      $activityid = $this->activityid;
    }
    $result = array();
    $this->db->select('review_overall as rating,review_name as review_by,review_comment,review_date');
    $this->db->where('review_status', 'Yes');
    $this->db->where('review_module', 'activity');
    $this->db->where('review_itemid', $activityid);
    $this->db->order_by('review_id', 'desc');
    $rs = $this->db->get('pt_reviews')->result();
    foreach ($rs as $r) {
      $result[] = array("rating" => $r->rating, "review_by" => $r->review_by, "review_comment" => $r->review_comment, "review_date" => pt_show_date_php($r->review_date));
    }
    return $result;
  }
// activity  Reviews Averages
  function activityReviewsAvg($activityid) {
    if (empty($activityid)) {
      $activityid = $this->activityid;
    }
    $this->db->select("COUNT(*) AS totalreviews");
    $this->db->select_avg('review_overall', 'overall');
    $this->db->select_avg('review_clean', 'clean');
    $this->db->select_avg('review_facilities', 'facilities');
    $this->db->select_avg('review_staff', 'staff');
    $this->db->select_avg('review_comfort', 'comfort');
    $this->db->select_avg('review_location', 'location');
    $this->db->where('review_status', 'Yes');
    $this->db->where('review_module', 'activity');
    $this->db->where('review_itemid', $activityid);
    $res = $this->db->get('pt_reviews')->result();
    $clean = round($res[0]->clean, 1);
    $comfort = round($res[0]->comfort, 1);
    $location = round($res[0]->location, 1);
    $facilities = round($res[0]->facilities, 1);
    $staff = round($res[0]->staff, 1);
    $totalreviews = $res[0]->totalreviews;
    $overall = round($res[0]->overall, 1);
    $result = (object) array('clean' => $clean, 'comfort' => $comfort, 'location' => $location, 'facilities' => $facilities, 'staff' => $staff, 'totalReviews' => $totalreviews, 'overall' => $overall);
    return $result;
  }
// Activity visiting Cities
  function activity_visiting_cities() {
    $this->db->select('map_city_name');
    $this->db->where('map_city_type', 'visit');
    $this->db->where('map_activity_id', $this->activityid);
    return $this->db->get('pt_activity_maps')->result();
  }
  function translated_data($lang) {
    $this->db->where('item_id', $this->activityid);
    $this->db->where('trans_lang', $lang);
    return $this->db->get('pt_activity_translation')->result();
  }
  function is_featured($activityid) {
    if (empty($activityid)) {
      $activityid = $this->activityid;
    }
    else {
      $activityid = $activityid;
    }
    $this->db->select('activity_id');
    $this->db->where('activity_is_featured', 'yes');
    $this->db->where('activity_featured_from <', time());
    $this->db->where('activity_featured_to >', time());
    $this->db->or_where('activity_featured_forever', 'forever');
    $this->db->having('activity_id', $activityid);
    return $this->db->get('pt_activity')->num_rows();
  }
  function featured_activity_list() {
    $settings = $this->settings();
    $limit = $settings[0]->front_homepage;
    $orderby = $settings[0]->front_homepage_order;
    $this->db->select('activity_id,activity_order,activity_title,activity_status,activity_location');
    $this->db->where('activity_is_featured', 'yes');
    $this->db->where('activity_featured_from <', time());
    $this->db->where('activity_featured_to >', time());
    $this->db->or_where('activity_featured_forever', 'forever');
    $this->db->having('activity_status', 'Yes');
    $this->db->limit($limit);
    if ($orderby == "za") {
      $this->db->order_by('pt_activity.activity_title', 'desc');
    }
    elseif ($orderby == "az") {
      $this->db->order_by('pt_activity.activity_title', 'asc');
    }
    elseif ($orderby == "oldf") {
      $this->db->order_by('pt_activity.activity_id', 'asc');
    }
    elseif ($orderby == "newf") {
      $this->db->order_by('pt_activity.activity_id', 'desc');
    }
    elseif ($orderby == "ol") {
      $this->db->order_by('pt_activity.activity_order', 'asc');
    }
    return $this->db->get('pt_activity')->result();
  }
// activity Reviews
  function activityReviews($activityid = null) {
    if (empty($activityid)) {
      $activityid = $this->activityid;
    }
    $this->db->where('review_status', 'Yes');
    $this->db->where('review_module', 'activity');
    $this->db->where('review_itemid', $activityid);
    $this->db->order_by('review_id', 'desc');
    return $this->db->get('pt_reviews')->result();
  }
  function extrasTranslation($id, $title, $desc) {
    $language = $this->lang;
    $this->db->select('trans_title,trans_desc');
    $this->db->where('trans_extras_id', $id);
    $this->db->where('trans_lang', $language);
    $r = $this->db->get('pt_extras_translation')->result();
    if (empty($r[0]->trans_title)) {
      $result['title'] = $title;
    }
    else {
      $result['title'] = $r[0]->trans_title;
    }
    if (empty($r[0]->trans_desc)) {
      $result['desc'] = $desc;
    }
    else {
      $result['desc'] = $r[0]->trans_desc;
    }
    return $result;
  }
  function activityTypes() {
    $activitytypes = array();
    $this->db->select('sett_name,sett_id');
    $this->db->where('sett_type', 'ttypes');
    $types = $this->db->get('pt_activity_types_settings')->result();
    foreach ($types as $t) {
      $tname = $this->activityTypeSettings($t->sett_id);
      $activitytypes[] = (object) array('id' => $t->sett_id, 'name' => $tname->name);
    }
    return $activitytypes;
  }
// Activity Type
  function activityTypeSettings($id) {
    $language = $this->lang;
    $result = new stdClass;
    $this->db->select('sett_name,sett_img');
    $this->db->where('sett_id', $id);
    $this->db->where('sett_status', 'Yes');
    $re = $this->db->get('pt_activity_types_settings')->result();
    $result->icon = PT_ACTIVITY_ICONS . $re[0]->sett_img;
    $result->id = $id;
    if ($language == $this->langdef) {
      $result->name = $re[0]->sett_name;
    }
    else {
      $this->db->select('trans_name');
      $this->db->where('sett_id', $id);
      $this->db->where('trans_lang', $language);
      $r = $this->db->get('pt_activity_types_settings_translation')->result();
      if (empty($r[0]->trans_name)) {
        $result->name = $re[0]->sett_name;
      }
      else {
        $result->name = $r[0]->trans_name;
      }
    }
    return $result;
  }
//Populate Activity Types according to the location selected
  function getActivityTypesLocationBased($location) {
    $result = new stdClass;
    $result->hasResult = FALSE;
    $result->optionsList = "";
    $activityTypes = array();
    $activityIDs = array();
    $this->db->where('location_id', $location);
    $this->db->group_by('activity_id');
    $activity = $this->db->get('pt_activity_locations')->result();
    if (!empty($activity)) {
      foreach ($activity as $t) {
        $activityIDs[] = $t->activity_id;
      }
    }
    $this->db->select('activity_type');
//$this->db->where('activity_location',$location);
    if (!empty($activityIDs)) {
      $this->db->where_in('activity_id', $activityIDs);
    }
    else {
      $this->db->where('activity_id', '0');
    }
    $this->db->group_by('activity_type');
    $res = $this->db->get('pt_activity')->result();
    if (!empty($res)) {
      foreach ($res as $r) {
        $activityTypes[] = $r->activity_type;
      }
      $result->hasResult = TRUE;
      foreach ($activityTypes as $type) {
        $typeDetails = $this->activityTypeSettings($type);
        $result->optionsList .= "<option value='" . $typeDetails->id . "' selected>" . $typeDetails->name . "</option>";
        $result->types[] = array("id" => $typeDetails->id, "name" => $typeDetails->name);
      }
    }
    else {
      $result->hasResult = FALSE;
      $result->optionsList = "<option value='' selected> Select </option>";
    }
    return $result;
  }
  function activityLocations($id = null) {
    $result = new stdClass;
    if (empty($id)) {
      $id = $this->activityid;
    }
    $this->db->where('activity_id', $id);
    $locs = $this->db->get('pt_activity_locations')->result();
    foreach ($locs as $l) {
      $locInfo = pt_LocationsInfo($l->location_id, $this->lang);
      if (!empty($locInfo->city)) {
        $result->locations[] = (object) array('id' => $locInfo->id, 'name' => $locInfo->city, 'lat' => $locInfo->latitude, 'long' => $locInfo->longitude);
      }
    }
    return $result;
  }
//make a result object all data of activity array
  function getResultObject($activity) {
    $this->ci->load->library('currconverter');
    $result = array();
    $curr = $this->ci->currconverter;
    foreach ($activity as $t) {
      $this->set_id($t->activity_id);
      $this->activity_short_details();
      $adultprice = $this->adultPrice * $this->adults;
      $childprice = $this->childPrice;
      $infantprice = $this->infantPrice;
      $price = $curr->convertPrice($adultprice);
      $avgReviews = $this->activityReviewsAvg();
      $result[] = (object) array('id' => $this->activityid, 'title' => $this->title, 'slug' => base_url() . 'activity/' . $this->slug, 'thumbnail' => $this->thumbnail, 'stars' => pt_create_stars($this->stars), 'starsCount' => $this->stars, 'location' => $this->location, 'desc' => strip_tags($this->desc), 'price' => $price, 'currCode' => $curr->code, 'currSymbol' => $curr->symbol, 'inclusions' => $this->inclusions, 'avgReviews' => $avgReviews, 'latitude' => $this->latitude, 'longitude' => $this->longitude, 'activityDays' => $this->activityDays, 'activityNights' => $this->activityNights, 'activityType' => $this->activityType);
    }
    $this->currencycode = $curr->code;
    $this->currencysign = $curr->symbol;
    return $result;
  }
//make a result object limited data of activity array
  function getLimitedResultObject($activity) {
    $this->ci->load->library('currconverter');
    $result = array();
    $curr = $this->ci->currconverter;
    if (!empty($activity)) {
      foreach ($activity as $t) {
        $this->set_id($t->activity_id);
        $this->activity_short_details();
        $adultprice = $this->adultPrice * $this->adults;
        $childprice = $this->childPrice;
        $infantprice = $this->infantPrice;
        $avgReviews = $this->activityReviewsAvg();
        $price = $curr->convertPrice($adultprice);
        if (!empty($this->title)) {
          $result[] = (object) array('id' => $this->activityid, 'title' => $this->title, 'slug' => base_url() . 'activity/' . $this->slug, 'thumbnail' => $this->thumbnail, 'stars' => pt_create_stars($this->stars),'starsCount' => $this->stars, 'location' => $this->location, 'price' => $price, 'currCode' => $curr->code, 'currSymbol' => $curr->symbol, 'latitude' => $this->latitude, 'longitude' => $this->longitude, 'avgReviews' => $avgReviews,);
        }
      }
    }
    return $result;
  }
//make a result object of single activity
  function getSingleResultObject($id) {
    $this->ci->load->library('currconverter');
    $result = "";
    $curr = $this->ci->currconverter;
    if (!empty($id)) {
      $this->set_id($id);
      $this->activity_short_details();
      $adultprice = $this->adultPrice * $this->adults;
      $childprice = $this->childPrice;
      $infantprice = $this->infantPrice;
      $avgReviews = $this->activityReviewsAvg();
      $price = $curr->convertPrice($adultprice);
      if (!empty($this->title)) {
        $result = (object) array('id' => $this->activityid, 'title' => $this->title, 'slug' => base_url() . 'activity/' . $this->slug, 'thumbnail' => $this->thumbnail, 'stars' => pt_create_stars($this->stars), 'location' => $this->location, 'price' => $price, 'currCode' => $curr->code, 'currSymbol' => $curr->symbol, 'latitude' => $this->latitude, 'longitude' => $this->longitude, 'avgReviews' => $avgReviews,);
      }
    }
    return $result;
  }
//make a result object of booking info
  function getBookResultObject($activityid, $date = null, $adults = null, $child = null, $infants = null) {
    if (empty($date)) {
      $date = $this->date;
    }
    $extrasCheckUrl = base_url() . 'activity/activityajaxcalls/activityExtrasBooking';
    $this->ci->load->library('currconverter');
    $result = array();
    $curr = $this->ci->currconverter;
//activity details for booking page
    $this->set_id($activityid);
    $this->activity_short_details();
    $extras = $this->activityExtras();
    if (empty($adults)) {
      $adults = $this->adults;
    }
    if (empty($child)) {
      $child = $this->child;
    }
    if (empty($infants)) {
      $infants = $this->infants;
    }
    $adultPrice = $this->adultPrice * $adults;
    $childPrice = $this->childPrice * $child;
    $infantPrice = $this->infantPrice * $infants;
    $totalSum = $adultPrice + $childPrice + $infantPrice;
    $subTotal = $curr->convertPriceFloat($adultPrice) + $curr->convertPriceFloat($childPrice) + $curr->convertPriceFloat($infantPrice);
// $subTotal = $childPrice;
    $this->setTax($subTotal);
    $taxAmount = $curr->addComma($this->taxamount);
    $totalPrice = $subTotal + $this->taxamount;
    $price = $curr->addComma($totalPrice);
    $this->setDeposit($totalPrice);
    $depositAmount = $curr->addComma($this->deposit);
    $result["activity"] = (object) array('id' => $this->activityid, 'title' => $this->title, 'slug' => base_url() . 'activity/' . $this->slug, 'thumbnail' => $this->thumbnail, 'stars' => pt_create_stars($this->stars), 'starsCount' => $this->stars, 'location' => $this->location, 'date' => $date, 'metadesc' => $this->metadesc, 'keywords' => $this->keywords, 'extras' => $extras, 'taxAmount' => $taxAmount, 'depositAmount' => $depositAmount, 'policy' => $this->policy, 'extraChkUrl' => $extrasCheckUrl, 'adults' => $adults, 'children' => $child, 'infants' => $infants, 'activityDays' => $this->activityDays, 'activityNights' => $this->activityNights, 'currCode' => $curr->code, 'currSymbol' => $curr->symbol, 'price' => $price, 'adultprice' => $curr->convertPrice($adultPrice), 'childprice' => $curr->convertPrice($childPrice), 'infantprice' => $curr->convertPrice($infantPrice), 'subTotal' => $subTotal);
//end activity details for booking page
    return $result;
  }
  function setDeposit($total) {
    if ($this->comm_type == "fixed") {
      $this->deposit = round($this->comm_value, 2);
    }
    else {
      $this->deposit = round(($total * $this->comm_value) / 100, 2);
    }
  }
  function setTax($amount) {
    if ($this->tax_type == "fixed") {
      $this->taxamount = round($this->tax_value, 2);
    }
    else {
      $this->taxamount = round(($amount * $this->tax_value) / 100, 2);
    }
  }
  function extrasFee($exts) {
    $extFee = 0;
    $this->ci->load->library('currconverter');
    $curr = $this->ci->currconverter;
    foreach ($exts as $ext) {
      $this->db->select('extras_title,extras_desc,extras_discount,extras_basic_price');
      $this->db->where('extras_id', $ext);
      $rs = $this->db->get('pt_extras')->result();
      $amount = $rs[0]->extras_basic_price;
      $price = $curr->convertPriceFloat($amount);
      $extFee += $amount;
      $info = $this->extrasTranslation($ext, $rs[0]->extras_title, $rs[0]->extras_desc);
      $result['extrasIndividualFee'][] = array("id" => $ext, "price" => $price);
      $result['extrasInfo'][] = array("title" => $info['title'], "price" => $price);
    }
    $result['extrasTotalFee'] = $extFee;
    return $result;
  }
//get updated values of booking data after extras and payment method updates
  function getUpdatedDataBookResultObject($activityid, $adults = 1, $child = 0, $infant = 0, $extras) {
    $this->ci->load->library('currconverter');
    $result = array();
    $curr = $this->ci->currconverter;
    $extratotal = $this->extrasFee($extras);
    $extTotal = $extratotal['extrasTotalFee'];
    $paymethodTotal = 0; //$this->paymethodFee($this->ci->input->post('paymethod'),$total);
    $this->set_id($activityid);
    $this->activity_short_details();
    $adultPrice = $this->adultPrice * $adults;
    $childPrice = $this->childPrice * $child;
    $infantPrice = $this->infantPrice * $infant;
    $totalSum = $adultPrice + $childPrice + $infantPrice;
    $total = $curr->convertPriceFloat($extTotal) + $curr->convertPriceFloat($adultPrice) + $curr->convertPriceFloat($childPrice) + $curr->convertPriceFloat($infantPrice);
    $this->setTax($total);
    $taxAmount = $curr->addComma($this->taxamount);
    $grandTotal = $total + $paymethodTotal + $this->taxamount;
    $this->setDeposit($grandTotal);
    $depositAmount = $this->deposit;
    $price = $grandTotal;
//$perNight = $curr->convertPrice($roomprice['perNight'],0);
    $extrasHtml = "";
    foreach ($extratotal['extrasInfo'] as $einfo) {
      $extrasHtml .= "<tr class='allextras'><td>" . $einfo['title'] . "</td>
          					<td class='text-right'>" . $curr->code . " " . $curr->symbol . $curr->addComma($einfo['price']) . "</td></tr>";
    }
    $adultsSubitem = array("price" => $curr->convertPriceFloat($this->adultPrice), "count" => $adults);
    if ($child > 0) {
      $childSubitem = array("price" => $curr->convertPriceFloat($this->childPrice), "count" => $child);
    }
    else {
      $childSubitem = "";
    }
    if ($infant > 0) {
      $infantSubitem = array("price" => $curr->convertPriceFloat($this->infantPrice), "count" => $infant);
    }
    else {
      $infantSubitem = "";
    }
    $subitem = array("adults" => $adultsSubitem, "child" => $childSubitem, "infant" => $infantSubitem);
    $result = (object) array('grandTotal' => $price, 'taxAmount' => $taxAmount, 'depositAmount' => $depositAmount, 'extrashtml' => $extrasHtml, 'bookingType' => "activity", 'currCode' => $curr->code, 'stay' => 1, 'currSymbol' => $curr->symbol, 'subitem' => $subitem, 'extrasInfo' => $extratotal);
//end activity details for booking page
    return json_encode($result);
  }
  public function checkErrors($maxAdults, $maxChild, $maxInfant) {
    if ($maxAdults < $this->adults) {
      $this->error = true;
      $this->errorCode = "0455";
    }
    elseif ($maxChild < $this->child) {
      $this->error = true;
      $this->errorCode = "0456";
    }
    elseif ($maxInfant < $this->infants) {
      $this->error = true;
      $this->errorCode = "0457";
    }
  }
//convert price
  public function convertAmount($price) {
    $this->ci->load->library('currconverter');
    $curr = $this->ci->currconverter;
    return $curr->convertPriceFloat($price);
  }
  public function convertPriceRange($price) {
    $this->ci->load->library('currconverter');
    $curr = $this->ci->currconverter;
    return $curr->convertPriceRange($price, 0);
  }
  public function priceRange($sprice) {
    $result = "";
    if (!empty($sprice)) {
      $sprice = str_replace(";", ",", $sprice);
      $sprice = explode(",", $sprice);
      $minprice = $this->convertPriceRange($sprice[0]);
      $maxprice = $this->convertPriceRange($sprice[1]);
      $result = $minprice . "-" . $maxprice;
    }
    return $result;
  }
  public function activityByLocations($totalnums = 7) {
    $locData = new stdClass;
    $this->db->select('location_id,activity_id');
    $this->db->where('position', '1');
    $this->db->group_by('location_id');
    $result = $this->db->get('pt_activity_locations')->result();
    foreach ($result as $rs) {
      $this->db->select('activity_id');
      $this->db->where('activity_location', $rs->location_id);
      $this->db->where('activity_status', 'Yes');
      $activity = $this->db->get('pt_activity')->result();
/*$activityData = $this->getSingleResultObject($rs->activity_id);*/
      $locationInfo = pt_LocationsInfo($rs->location_id, $this->lang);
      $locData->locations[] = (object) array('id' => $rs->location_id, 'name' => $locationInfo->city, 'count' => count($activity), 'activity' => $activity);
    }
    usort($locData->locations, array($this, "cmp"));
    $locData->locations = array_slice($locData->locations, 0, $totalnums);
    return $locData;
  }
  function cmp($a, $b) {
    return $a->count < $b->count;
  }
  public function siteMapData() {
    $activityData = array();
    $this->db->select('activity_id');
    $this->db->where('activity_status', 'Yes');
    $result = $this->db->get('pt_activity');
    $activity = $result->result();
    if (!empty($activity)) {
      $activityData = $this->getLimitedResultObject($activity);
    }
    return $activityData;
  }
  public function suggestionResults($query) {
    $response = array();
    $this->db->select('pt_activity_translation.trans_title as title, pt_activity.activity_id as id,pt_activity.activity_title as title');
    $this->db->like('pt_activity.activity_title', $query);
    $this->db->or_like('pt_activity_translation.trans_title', $query);
    $this->db->join('pt_activity_translation', 'pt_activity.activity_id = pt_activity_translation.item_id', 'left');
    $this->db->group_by('pt_activity.activity_id');
    $this->db->limit('25');
    $res = $this->db->get('pt_activity')->result();
    $activity = array();
    $locations = array();
    $this->db->select('pt_locations.id,pt_locations.location');
    $this->db->like('pt_locations.location', $query);
//$this->db->or_like('pt_locations.country',$query);
    $this->db->limit('25');
    $this->db->group_by('pt_locations.id');
    $this->db->join('pt_activity_locations', 'pt_locations.id = pt_activity_locations.location_id');
    $locres = $this->db->get('pt_locations')->result();
    if (!empty($locres)) {
    foreach ($locres as $l) {
        $lc++;
        $locInfo = pt_LocationsInfo($l->id, $this->lang);
        $locations[] = (object) array('id' => $l->id, 'text' => $locInfo->city.", ".$locInfo->country, 'module' => 'location', 'disabled' => false);
      }
    }
    if (!empty($res)) {
    foreach ($res as $r) {
        $title = $this->get_title($r->title, $r->id);
        $activity[] = (object) array('id' => $r->id, 'text' => trim($title), 'module' => 'activity', 'disabled' => false);
      }
    }
    $tt = array("text" => "Activity", "children" => $activity);
    $ll = array("text" => "Locations", "children" => $locations);
    if(!empty($activity)){
      $response[] = $tt;
    }
  if(!empty($locations)){
    $response[] = $ll;
  }

    $dataResponse = $response;
    return $dataResponse;
  }
  function getLatestActivityForAPI() {
    $this->ci->db->select('activity_id,activity_created_at');
    $this->ci->db->order_by('activity_created_at', 'desc');
    $this->ci->db->limit('10');
    $items = $this->ci->db->get('pt_activity')->result();
    $this->ci->load->library('currconverter');
    $result = array();
    $curr = $this->ci->currconverter;
    if (!empty($items)) {
      foreach ($items as $h) {
        $this->set_id($h->activity_id);
        $this->activity_short_details();
        $adultprice = $this->adultPrice * $this->adults;
        $price = $curr->convertPrice($adultprice);
        $avgReviews = $this->activityReviewsAvg();
        if (!empty($this->title)) {
          $result[] = (object) array('id' => $h->activity_id, 'title' => $this->title, 'thumbnail' => $this->thumbnail, 'starsCount' => $this->stars,'location' => $this->location, 'price' => $price, 'currCode' => $curr->code, 'currSymbol' => $curr->symbol, 'avgReviews' => $avgReviews, 'createdAt' => $this->createdAt, 'module' => 'activity');
        }
      }
    }
    return $result;
  }
}
