<?php
class Advertising_lib {
/**
* Protected variables
*/
  protected $ci = NULL; //codeigniter instance
  protected $db; //database instatnce instance
  public $appSettings;
  public $advertisingid;
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

  //add
  public $destination;
  public $wish;
  //end add

  public $latitude;
  public $longitude;
  public $isfeatured;
  public $thumbnail;
  public $inclusions;
  public $exclusions;
  public $adultStatus;
  public $childStatus;
  public $infantStatus;
  public $advertisingNights;
  public $advertisingDays;
  public $advertisingType;
  public $adults;
  public $child;
  public $infants;
  public $selectedLocation;
  public $selectedAdvertisingType;
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
    $this->ci->load->model('Advertising/Advertising_model');
    $this->ci->load->helper('Advertising/advertising_front');
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
    $this->selectedAdvertisingType = $this->selectedAdvertisingType($typeid);
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
  function set_advertisingid($advertisinglug) {
    $this->db->select('advertising_id');
    $this->db->where('advertising_slug', $advertisinglug);
    $r = $this->db->get('pt_advertising')->result();
    $this->advertisingid = $r[0]->advertising_id;
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
//set advertising id by id
  function set_id($id, $currsign = null, $currcode = null) {
    $this->advertisingid = $id;
    $this->currencysign = $currsign;
    $this->currencycode = $currcode;
  }
  function get_id() {
    return $this->advertisingid;
  }
  function settings() {
    return $this->ci->Settings_model->get_front_settings('advertising');
  }
  function wishListInfo($id) {
    $this->advertising_short_details($id);
    $title = $this->title;
    $slug = base_url() . 'advertising/' . $this->slug;
    $thumbnail = $this->thumbnail;
    $location = $this->location;
    $stars = pt_create_stars($this->stars);
    $res = array("title" => $title, "slug" => $slug, "thumbnail" => $thumbnail, "location" => $location->city, "stars" => $stars,);
    return $res;
  }

  function selectedAdvertisingType($id) {
    $option = "";
    if (!empty($id)) {
      $res = $this->advertisingTypeSettings($id);
      if (!empty($res->name)) {
        $option = "<option value=" . $res->id . " selected >" . $res->name . "</option>";
      }
    }
    return $option;
  }
  function show_advertising($offset = null) {
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
    $rh = $this->ci->Advertising_model->list_advertising_front($priceRange);
    $advertising = $this->ci->Advertising_model->list_advertising_front($priceRange, $perpage, $offset, $orderby);
    $data['all_advertising'] = $this->getResultObject($advertising['all']);
    $data['paginationinfo'] = array('base' => 'advertising/listing', 'totalrows' => $rh['rows'], 'perpage' => $perpage,'urisegment' => $totalSegments);
    return $data;
  }
  function showAdvertisingByLocation($locs, $offset = null) {
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
    $rh = $this->ci->Advertising_model->showAdvertisingByLocation($locs->locations, $priceRange);
    $advertising = $this->ci->Advertising_model->showAdvertisingByLocation($locs->locations, $priceRange, $perpage, $offset, $orderby);
    $data['all_advertising'] = $this->getResultObject($advertising['all']);
    $data['paginationinfo'] = array('base' => 'advertising/' . $locs->urlBase, 'totalrows' => $rh['rows'], 'perpage' => $perpage, 'urisegment' => $locs->uriSegment);
    return $data;
  }



  function search_advertising($location, $offset = null) {
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
    $rh = $this->ci->Advertising_model->search_advertising_front($location, $priceRange);
    $advertising = $this->ci->Advertising_model->search_advertising_front($location, $priceRange, $perpage, $offset, $orderby);
    $data['all_advertising'] = $this->getResultObject($advertising['all']);
    $data['paginationinfo'] = array('base' => 'advertising/search' . $segments, 'totalrows' => $rh['rows'], 'perpage' => $perpage, 'urisegment' => $urisegment);
    return $data;
  }


  function advertising_details($advertisingid = null, $date = null) {
    if (empty($advertisingid)) {
      $advertisingid = $this->advertisingid;
    }
    else {
      $advertisingid = $advertisingid;
    }
    $this->ci->load->library('currconverter');
    $curr = $this->ci->currconverter;
    if (!empty($date)) {
      $this->date = $date;
    }
    $this->db->where('advertising_id', $advertisingid);
    $details = $this->db->get('pt_advertising')->result();
    $title = $this->get_title($details[0]->advertising_title, $details[0]->advertising_id);
    $stars = $details[0]->advertising_stars;
    $desc = $this->get_description($details[0]->advertising_desc, $details[0]->advertising_id);
    $policy = $this->get_policy($details[0]->advertising_privacy, $details[0]->advertising_id);
    $locationInfoUrl = pt_LocationsInfo($details[0]->advertising_location);

    $countryName = url_title($locationInfoUrl->country, 'dash', true);
    $cityName = url_title($locationInfoUrl->city, 'dash', true);

    $slug = $countryName . '/' . $cityName . '/' . $details[0]->advertising_slug . $this->urlVars;

    $bookingSlug = $details[0]->advertising_slug . $this->urlVars;
    $keywords = $this->get_keywords($details[0]->advertising_meta_keywords, $details[0]->advertising_id);
    $metadesc = $this->get_metaDesc($details[0]->advertising_meta_desc, $details[0]->advertising_id);
    $advertisingDays = $details[0]->advertising_days;
    $advertisingNights = $details[0]->advertising_nights;
    if (!empty($details[0]->advertising_amenities)) {
      $advertisingAmenities = explode(",", $details[0]->advertising_amenities);
      foreach ($advertisingAmenities as $tm) {
        $amts[] = $this->advertisingTypeSettings($tm);
      }
    }
    else {
      $amts = array();
    }
    $inclusions = $amts;
    if (!empty($details[0]->advertising_exclusions)) {
      $advertisingExclusions = explode(",", $details[0]->advertising_exclusions);
      foreach ($advertisingExclusions as $exc) {
        $excs[] = $this->advertisingTypeSettings($exc);
      }
    }
    else {
      $excs = array();
    }
    $exclusions = $excs;
    if (!empty($details[0]->advertising_payment_opt)) {
      $advertisingPaymentOpts = explode(",", $details[0]->advertising_payment_opt);
      foreach ($advertisingPaymentOpts as $p) {
        $payopts[] = $this->advertisingTypeSettings($p);
      }
    }
    else {
      $payopts = array();
    }
    $paymentOptions = $payopts;
    if (!empty($details[0]->advertising_related)) {
      $radvertising = explode(",", $details[0]->advertising_related);
    }
    else {
      $radvertising = "";
    }

    if (!empty($details[0]->advertising_nearby_related)) {
      $nearbyradvertising = explode(",", $details[0]->advertising_nearby_related);
    }
    else {
      $nearbyradvertising = "";
    }


    $relatedAdvertising = $this->getRelatedAdvertising($radvertising);
    $nearbyrelatedAdvertising = $this->getNearbyRelatedAdvertising($nearbyradvertising);
    $thumbnail = PT_ADVERTISING_SLIDER_THUMB . $details[0]->thumbnail_image;
    $city = pt_LocationsInfo($details[0]->advertising_location, $this->lang);
    $location = $city->city; // $details[0]->advertising_location;
//	$isfeatured = $this->is_featured();
 //$destination = pt_DestinationInfo($details[0]->advertising_destination, $this->lang);
$destination = $destination->destination_name; // $details[0]->advertising_location;
//	$isfeatured = $this->is_featured();

    $website = $details[0]->advertising_website;
    $phone = $details[0]->advertising_phone;
    $email = $details[0]->advertising_email;
    $taxcom = $this->advertising_tax_commision();
    $comm_type = $taxcom['commtype'];
    $comm_value = $taxcom['commval'];
    $tax_type = $taxcom['taxtype'];
    $tax_value = $taxcom['taxval'];
    $latitude = $details[0]->advertising_latitude;
    $longitude = $details[0]->advertising_longitude;
    $totalAdutlsPrice = $details[0]->advertising_adult_price * $this->adults;
    $totalChildPrice = $details[0]->advertising_child_price * $this->child;
    $totalInfantsPrice = $details[0]->advertising_infant_price * $this->infants;
    $adultPrice = $curr->convertPrice($totalAdutlsPrice);
    $childPrice = $curr->convertPrice($totalChildPrice);
    $infantPrice = $curr->convertPrice($totalInfantsPrice);
    $perAdultPrice = $curr->convertPrice($details[0]->advertising_adult_price);
    $perChildPrice = $curr->convertPrice($details[0]->advertising_child_price);
    $perInfantPrice = $curr->convertPrice($details[0]->advertising_infant_price);
    $maxAdults = $details[0]->advertising_max_adults;
    $maxChild = $details[0]->advertising_max_child;
    $maxInfant = $details[0]->advertising_max_infant;
    $this->checkErrors($maxAdults, $maxChild, $maxInfant);
    $adultStatus = $details[0]->adult_status;
    $childStatus = $details[0]->child_status;
    $infantStatus = $details[0]->infant_status;
    $sliderImages = $this->advertisingImages($details[0]->advertising_id);
    $totalCost = $totalAdutlsPrice + $totalChildPrice + $totalInfantsPrice;
    $taxcom = $this->advertising_tax_commision($details[0]->advertising_id);
    $this->comm_type = $taxcom['commtype'];
    $this->comm_value = $taxcom['commval'];
    $this->tax_type = $taxcom['taxtype'];
    $this->tax_value = $taxcom['taxval'];
    $this->setDeposit($curr->convertPriceFloat($totalCost, 2));
    $depositAmount = $this->deposit;
    $detailResults = (object) array('id' => $details[0]->advertising_id, 'title' => $title, 'slug' => $slug, 'bookingSlug' => $bookingSlug, 'thumbnail' => $thumbnail, 'stars' => pt_create_stars($stars), 'starsCount' => $stars, 'location' => $location, 'desc' => $desc, 'inclusions' => $inclusions, 'exclusions' => $exclusions, 'latitude' => $latitude, 'longitude' => $longitude, 'sliderImages' => $sliderImages, 'relatedItems' => $relatedAdvertising,'nearbyrelatedItems' => $nearbyrelatedAdvertising,  'paymentOptions' => $paymentOptions, 'metadesc' => $metadesc, 'keywords' => $keywords, 'policy' => $policy, 'website' => $website, 'email' => $email, 'phone' => $phone, 'maxAdults' => $maxAdults, 'maxChild' => $maxChild, 'maxInfant' => $maxInfant, 'adultStatus' => $adultStatus, 'childStatus' => $childStatus, 'infantStatus' => $infantStatus, 'adultPrice' => $adultPrice, 'childPrice' => $childPrice, 'infantPrice' => $infantPrice, 'perAdultPrice' => $perAdultPrice, 'perChildPrice' => $perChildPrice, 'perInfantPrice' => $perInfantPrice, 'currCode' => $curr->code, 'currSymbol' => $curr->symbol, 'date' => $this->date, 'totalCost' => $curr->convertPrice($totalCost), 'comType' => $comm_type, 'comValue' => $comm_value, 'taxType' => $tax_type, 'taxValue' => $tax_value, 'advertisingDays' => $advertisingDays, 'advertisingNights' => $advertisingNights, 'totalDeposit' => $depositAmount, 'mapAddress' => $details[0]->advertising_mapaddress);
    return $detailResults;
  }

  function advertising_short_details($advertisingid) {
    if (empty($advertisingid)) {
      $advertisingid = $this->advertisingid;
    }
    $this->db->select('advertising_title,advertising_stars,advertising_slug,advertising_desc,advertising_privacy,advertising_max_adults,advertising_max_child,
   advertising_max_infant,advertising_basic_price,advertising_basic_discount,advertising_adult_price,advertising_child_price,advertising_infant_price,advertising_amenities,advertising_exclusions,advertising_days,advertising_nights,thumbnail_image,advertising_location,advertising_destination,advertising_latitude,advertising_longitude,advertising_type,advertising_created_at');
    $this->db->where('advertising_id', $advertisingid);
    $details = $this->db->get('pt_advertising')->result();
/*
    var_dump($details);
    exit();
    */
    $this->stars = $details[0]->advertising_stars;
    $this->title = $this->get_title($details[0]->advertising_title);
    $this->desc = $this->get_description($details[0]->advertising_desc);
    $this->policy = $this->get_policy($details[0]->advertising_privacy);
    $this->advertisingDays = $details[0]->advertising_days;
    $this->advertisingNights = $details[0]->advertising_nights;
    $this->advertisingNights = $details[0]->advertising_nights;
    $this->createdAt = $details[0]->advertising_created_at;
    $maxAdults = $details[0]->advertising_max_adults;
    $maxChild = $details[0]->advertising_max_child;
    $maxInfant = $details[0]->advertising_max_infant;
    $this->checkErrors($maxAdults, $maxChild, $maxInfant);
    $locationInfoUrl = pt_LocationsInfo($details[0]->advertising_location);
    $countryName = url_title($locationInfoUrl->country, 'dash', true);
    $cityName = url_title($locationInfoUrl->city, 'dash', true);
    $this->slug = $destinationName .'/'. $countryName . '/' . $cityName . '/' . $details[0]->advertising_slug . $this->urlVars;
    $this->bookingSlug = $details[0]->advertising_slug . $this->urlVars;

    $this->latitude = $details[0]->advertising_latitude;
    $this->longitude = $details[0]->advertising_longitude;
    $this->thumbnail = PT_ADVERTISING_SLIDER_THUMB . $details[0]->thumbnail_image;
    $type = $this->advertisingTypeSettings($details[0]->advertising_type);
    $this->advertisingType = $type->name;
    $taxcom = $this->advertising_tax_commision();
    $this->comm_type = $taxcom['commtype'];
    $this->comm_value = $taxcom['commval'];
    $this->tax_type = $taxcom['taxtype'];
    $this->tax_value = $taxcom['taxval'];
    $this->adultPrice = $details[0]->advertising_adult_price;
    $this->childPrice = $details[0]->advertising_child_price;
    $this->infantPrice = $details[0]->advertising_infant_price;
    $this->isfeatured = $this->is_featured();
    return $details;
  }
  function advertising_tax_commision($advertisingid = null) {
    if (empty($advertisingid)) {
      $advertisingid = $this->advertisingid;
    }
    $res = array();
    $this->db->select('advertising_comm_fixed,advertising_comm_percentage,advertising_tax_fixed,advertising_tax_percentage');
    $this->db->where('advertising_id', $advertisingid);
    $result = $this->db->get('pt_advertising')->result();
    $commfixed = $result[0]->advertising_comm_fixed;
    $commper = $result[0]->advertising_comm_percentage;
    $taxfixed = $result[0]->advertising_tax_fixed;
    $taxper = $result[0]->advertising_tax_percentage;
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
// get advertising images
  function advertisingImages($advertisingid) {
    if (empty($advertisingid)) {
      $advertisingid = $this->advertisingid;
    }
    $this->db->where('timg_advertising_id', $advertisingid);
    $this->db->where('timg_approved', '1');
    $this->db->order_by('timg_order', 'asc');
    $res = $this->db->get('pt_advertising_images')->result();
    if (empty($res)) {
      $result[] = array("fullImage" => PT_ADVERTISING_SLIDER_THUMB . PT_BLANK_IMG, "thumbImage" => PT_ADVERTISING_SLIDER_THUMB . PT_BLANK_IMG);
    }
    else {
      foreach ($res as $r) {
        $result[] = array("fullImage" => PT_ADVERTISING_SLIDER . $r->timg_image, "thumbImage" => PT_ADVERTISING_SLIDER_THUMB . $r->timg_image);
      }
    }
    return $result;
  }

  function getFeaturedAdvertising() {
    $advertising = $this->featured_advertising_list();
    $result = $this->getResultObject($advertising);
    return $result;
  }

  function getLocationBasedFeaturedAdvertising($loc) {
    $settings = $this->settings();
    $limit = $settings[0]->front_homepage;
    $this->db->select('advertising_id');
    $this->db->where('advertising_location', $loc);
    $this->db->where('advertising_status', 'Yes');
    $advertisingList = $this->db->get('pt_advertising')->result();
    $advertising = array();
    foreach ($advertisingList as $t) {
      $isFeatured = $this->is_featured($t->advertising_id);
      if ($isFeatured) {
        $advertising[] = (object) array('advertising_id' => $t->advertising_id);
      }
    }
    $advertising = array_slice($advertising, 0, $limit);
    $result = $this->getResultObject($advertising);
    return $result;
  }
  function getTopRatedAdvertising() {
    $advertising = $this->ci->Advertising_model->popular_advertising_front();
    $result = $this->getResultObject($advertising);
    return $result;
  }
  function getRelatedAdvertising($advertising) {
    $resultadvertising = array();
    $result = array();
    if (!empty($advertising)) {
      foreach ($advertising as $t) {
        $resultadvertising[] = (object) array('advertising_id' => $t);
      }
    }
    $result = $this->getLimitedResultObject($resultadvertising);
    return $result;
  }


  function getNearbyRelatedAdvertising($advertising) {
    $resultadvertising = array();
    $result = array();
    if (!empty($advertising)) {
      foreach ($advertising as $t) {
        $resultadvertising[] = (object) array('advertising_id' => $t);
      }
    }
    $result = $this->getLimitedResultObject($resultadvertising);
    return $result;
  }


// Get Advertising updated Price on changing adults, child and infant count.
  function updatedPrice($advertisingid, $adults = 1, $child = 0, $infant = 0) {
    $this->ci->load->library('currconverter');
    $curr = $this->ci->currconverter;
    $this->db->select('advertising_adult_price,advertising_child_price,advertising_infant_price');
    $this->db->where('advertising_id', $advertisingid);
    $details = $this->db->get('pt_advertising')->result();
    $totalAdutlsPrice = $details[0]->advertising_adult_price * $adults;
    $totalChildPrice = $details[0]->advertising_child_price * $child;
    $totalInfantsPrice = $details[0]->advertising_infant_price * $infant;
    $adultPrice = $curr->convertPrice($totalAdutlsPrice);
    $childPrice = $curr->convertPrice($totalChildPrice);
    $infantPrice = $curr->convertPrice($totalInfantsPrice);
    $totalCost = $totalAdutlsPrice + $totalChildPrice + $totalInfantsPrice;
    $taxcom = $this->advertising_tax_commision($advertisingid);
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
    $detailResults = array('id' => $advertisingid, 'adultPrice' => $adultPrice, 'childPrice' => $childPrice, 'infantPrice' => $infantPrice, 'currCode' => $curr->code, 'currSymbol' => $currSymbol, 'totalDeposit' => $curr->convertPrice($depositAmount), 'totalCost' => $curr->convertPrice($totalCost));
    return json_encode($detailResults);
  }
  function get_thumbnail() {
    $res = $this->ci->Advertising_model->default_advertising_img($this->advertisingid);
    if (!empty($res)) {
      return PT_ADVERTISING_SLIDER_THUMB . $res;
    }
    else {
      return PT_BLANK;
    }
  }
  function get_title($deftitle, $advertisingid) {
    if (empty($advertisingid)) {
      $advertisingid = $this->advertisingid;
    }
    if ($this->lang == $this->langdef) {
      $title = $deftitle;
    }
    else {
      $this->db->where('item_id', $advertisingid);
      $this->db->where('trans_lang', $this->lang);
      $res = $this->db->get('pt_advertising_translation')->result();
      $title = $res[0]->trans_title;
      if (empty($title)) {
        $title = $deftitle;
      }
    }
    return $title;
  }
  function get_description($defdesc, $advertisingid) {
    if (empty($advertisingid)) {
      $advertisingid = $this->advertisingid;
    }
    if ($this->lang == $this->langdef) {
      $desc = $defdesc;
    }
    else {
      $this->db->where('item_id', $advertisingid);
      $this->db->where('trans_lang', $this->lang);
      $res = $this->db->get('pt_advertising_translation')->result();
      $desc = $res[0]->trans_desc;
      if (empty($desc)) {
        $desc = $defdesc;
      }
    }
    return $desc;
  }


  function get_policy($defpolicy, $advertisingid) {
    if (empty($advertisingid)) {
      $advertisingid = $this->advertisingid;
    }
    if ($this->lang == $this->langdef) {
      $policy = $defpolicy;
    }
    else {
      $this->db->where('item_id', $advertisingid);
      $this->db->where('trans_lang', $this->lang);
      $res = $this->db->get('pt_advertising_translation')->result();
      $policy = $res[0]->trans_policy;
      if (empty($policy)) {
        $policy = $defpolicy;
      }
    }
    return $policy;
  }



  function get_keywords($defkeywords, $advertisingid = null) {
    if (empty($advertisingid)) {
      $advertisingid = $this->advertisingid;
    }
    if ($this->lang == $this->langdef) {
      $keywords = $defkeywords;
    }
    else {
      $this->db->where('item_id', $advertisingid);
      $this->db->where('trans_lang', $this->lang);
      $res = $this->db->get('pt_advertising_translation')->result();
      $keywords = $res[0]->metakeywords;
      if (empty($keywords)) {
        $keywords = $defkeywords;
      }
    }
    return $keywords;
  }
  function get_metaDesc($defmeta, $advertisingid = null) {
    if (empty($advertisingid)) {
      $advertisingid = $this->advertisingid;
    }
    if ($this->lang == $this->langdef) {
      $meta = $defmeta;
    }
    else {
      $this->db->where('item_id', $advertisingid);
      $this->db->where('trans_lang', $this->lang);
      $res = $this->db->get('pt_advertising_translation')->result();
      $meta = $res[0]->metadesc;
      if (empty($meta)) {
        $meta = $defmeta;
      }
    }
    return $meta;
  }
  function advertisingExtras($advertisingid = null) {
    if (empty($advertisingid)) {
      $advertisingid = $this->advertisingid;
    }
    $today = time();
    $result = array();
//	$this->db->where('extras_from  <=', $today);
//	$this->db->where('extras_to >=', $today);
    $this->db->where('extras_module', 'advertising');
//  $this->db->or_where('extras_forever','forever');
    $this->db->order_by('extras_id', 'desc');
    $this->db->like('extras_for', $advertisingid, 'both');
    $this->db->having('extras_status', 'Yes');
    $ext = $this->db->get('pt_extras')->result();
    $this->ci->load->library('currconverter');
    $curr = $this->ci->currconverter;
    if (!empty($ext)) {
      foreach ($ext as $e) {
        $trans = $this->extrasTranslation($e->extras_id, $e->extras_title, $e->extras_desc);
        $price = $curr->convertPrice($e->extras_basic_price);
        $result[] = (object) array("id" => $e->extras_id, "extraTitle" => $trans['title'], "extraDesc" => $trans['desc'], 'extraPrice' => $price, 'thumbnail' => PT_ADVERTISING_EXTRAS_IMAGES . $e->extras_image);
      }
    }
    return $result;
  }


  function getDestinationList() {
    $this->db->select('destination_id');
    $destinations = $this->db->get('pt_destinations')->result();
    foreach ($destinations as $r) {
    //  $destinationinfo = pt_DestinationInfo($r->destination_id, $this->lang);
      $resultDestination[] = (object) array('id' => $destinationinfo->destination_id, 'name' => $destinationinfo->destination_slug);
    }
print_r($resultDestination);
    return $resultDestination;
  }


  function getLocationsList() {
    $resultLocations = array();
    $this->db->select('location_id');
    $this->db->group_by('location_id');
    $locations = $this->db->get('pt_advertising_locations')->result();
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
// advertising Reviews
  function advertising_reviews($advertisingid) {
    if (empty($advertisingid)) {
      $advertisingid = $this->advertisingid;
    }
    $this->db->where('review_status', 'Yes');
    $this->db->where('review_module', 'advertising');
    $this->db->where('review_itemid', $advertisingid);
    $this->db->order_by('review_id', 'desc');
    return $this->db->get('pt_reviews')->result();
  }
// advertising Reviews for API
  function advertising_reviews_for_api($advertisingid) {
    if (empty($advertisingid)) {
      $advertisingid = $this->advertisingid;
    }
    $result = array();
    $this->db->select('review_overall as rating,review_name as review_by,review_comment,review_date');
    $this->db->where('review_status', 'Yes');
    $this->db->where('review_module', 'advertising');
    $this->db->where('review_itemid', $advertisingid);
    $this->db->order_by('review_id', 'desc');
    $rs = $this->db->get('pt_reviews')->result();
    foreach ($rs as $r) {
      $result[] = array("rating" => $r->rating, "review_by" => $r->review_by, "review_comment" => $r->review_comment, "review_date" => pt_show_date_php($r->review_date));
    }
    return $result;
  }
// advertising  Reviews Averages
  function advertisingReviewsAvg($advertisingid) {
    if (empty($advertisingid)) {
      $advertisingid = $this->advertisingid;
    }
    $this->db->select("COUNT(*) AS totalreviews");
    $this->db->select_avg('review_overall', 'overall');
    $this->db->select_avg('review_clean', 'clean');
    $this->db->select_avg('review_facilities', 'facilities');
    $this->db->select_avg('review_staff', 'staff');
    $this->db->select_avg('review_comfort', 'comfort');
    $this->db->select_avg('review_location', 'location');
    $this->db->where('review_status', 'Yes');
    $this->db->where('review_module', 'advertising');
    $this->db->where('review_itemid', $advertisingid);
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
// Advertising visiting Cities
  function advertising_visiting_cities() {
    $this->db->select('map_city_name');
    $this->db->where('map_city_type', 'visit');
    $this->db->where('map_advertising_id', $this->advertisingid);
    return $this->db->get('pt_advertising_maps')->result();
  }
  function translated_data($lang) {
    $this->db->where('item_id', $this->advertisingid);
    $this->db->where('trans_lang', $lang);
    return $this->db->get('pt_advertising_translation')->result();
  }
  function is_featured($advertisingid) {
    if (empty($advertisingid)) {
      $advertisingid = $this->advertisingid;
    }
    else {
      $advertisingid = $advertisingid;
    }
    $this->db->select('advertising_id');
    $this->db->where('advertising_is_featured', 'yes');
    $this->db->where('advertising_featured_from <', time());
    $this->db->where('advertising_featured_to >', time());
    $this->db->or_where('advertising_featured_forever', 'forever');
    $this->db->having('advertising_id', $advertisingid);
    return $this->db->get('pt_advertising')->num_rows();
  }

  function featured_advertising_list() {
    $settings = $this->settings();
    $limit = $settings[0]->front_homepage;
    $orderby = $settings[0]->front_homepage_order;
    $this->db->select('advertising_id,advertising_order,advertising_title,advertising_status,advertising_location,advertising_destination');
    $this->db->where('advertising_is_featured', 'yes');
    $this->db->where('advertising_featured_from <', time());
    $this->db->where('advertising_featured_to >', time());
    $this->db->or_where('advertising_featured_forever', 'forever');
    $this->db->having('advertising_status', 'Yes');
    $this->db->limit($limit);
    if ($orderby == "za") {
      $this->db->order_by('pt_advertising.advertising_title', 'desc');
    }
    elseif ($orderby == "az") {
      $this->db->order_by('pt_advertising.advertising_title', 'asc');
    }
    elseif ($orderby == "oldf") {
      $this->db->order_by('pt_advertising.advertising_id', 'asc');
    }
    elseif ($orderby == "newf") {
      $this->db->order_by('pt_advertising.advertising_id', 'desc');
    }
    elseif ($orderby == "ol") {
      $this->db->order_by('pt_advertising.advertising_order', 'asc');
    }
    return $this->db->get('pt_advertising')->result();
  }
  //end  featured_advertising_list


// advertising Reviews
  function advertisingReviews($advertisingid = null) {
    if (empty($advertisingid)) {
      $advertisingid = $this->advertisingid;
    }
    $this->db->where('review_status', 'Yes');
    $this->db->where('review_module', 'advertising');
    $this->db->where('review_itemid', $advertisingid);
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
  function advertisingTypes() {
    $advertisingtypes = array();
    $this->db->select('sett_name,sett_id');
    $this->db->where('sett_type', 'ttypes');
    $types = $this->db->get('pt_advertising_types_settings')->result();
    foreach ($types as $t) {
      $tname = $this->advertisingTypeSettings($t->sett_id);
      $advertisingtypes[] = (object) array('id' => $t->sett_id, 'name' => $tname->name);
    }
    return $advertisingtypes;
  }
// Advertising Type
  function advertisingTypeSettings($id) {
    $language = $this->lang;
    $result = new stdClass;
    $this->db->select('sett_name,sett_img');
    $this->db->where('sett_id', $id);
    $this->db->where('sett_status', 'Yes');
    $re = $this->db->get('pt_advertising_types_settings')->result();
    $result->icon = PT_ADVERTISING_ICONS . $re[0]->sett_img;
    $result->id = $id;
    if ($language == $this->langdef) {
      $result->name = $re[0]->sett_name;
    }
    else {
      $this->db->select('trans_name');
      $this->db->where('sett_id', $id);
      $this->db->where('trans_lang', $language);
      $r = $this->db->get('pt_advertising_types_settings_translation')->result();
      if (empty($r[0]->trans_name)) {
        $result->name = $re[0]->sett_name;
      }
      else {
        $result->name = $r[0]->trans_name;
      }
    }
    return $result;
  }
//Populate Advertising Types according to the location selected
  function getAdvertisingTypesLocationBased($location) {
    $result = new stdClass;
    $result->hasResult = FALSE;
    $result->optionsList = "";
    $advertisingTypes = array();
    $advertisingIDs = array();
    $this->db->where('location_id', $location);
    $this->db->group_by('advertising_id');
    $advertising = $this->db->get('pt_advertising_locations')->result();
    if (!empty($advertising)) {
      foreach ($advertising as $t) {
        $advertisingIDs[] = $t->advertising_id;
      }
    }
    $this->db->select('advertising_type');
//$this->db->where('advertising_location',$location);
    if (!empty($advertisingIDs)) {
      $this->db->where_in('advertising_id', $advertisingIDs);
    }
    else {
      $this->db->where('advertising_id', '0');
    }
    $this->db->group_by('advertising_type');
    $res = $this->db->get('pt_advertising')->result();
    if (!empty($res)) {
      foreach ($res as $r) {
        $advertisingTypes[] = $r->advertising_type;
      }
      $result->hasResult = TRUE;
      foreach ($advertisingTypes as $type) {
        $typeDetails = $this->advertisingTypeSettings($type);
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

  function advertisingLocations($id = null) {
    $result = new stdClass;
    if (empty($id)) {
      $id = $this->advertisingid;
    }
    $this->db->where('advertising_id', $id);
    $locs = $this->db->get('pt_advertising_locations')->result();
    foreach ($locs as $l) {
      $locInfo = pt_LocationsInfo($l->location_id, $this->lang);
      if (!empty($locInfo->city)) {
        $result->locations[] = (object) array('id' => $locInfo->id, 'name' => $locInfo->city, 'lat' => $locInfo->latitude, 'long' => $locInfo->longitude);
      }
    }
    return $result;
  }



  function advertisingDestination($id = null) {
    $result = new stdClass;
    if (empty($id)) {
      $id = $this->advertisingid;
    }
    $this->db->where('destination_id', $id);
    $locs = $this->db->get('pt_destinations')->result();
    foreach ($locs as $l) {
    //  $locInfo = pt_DestinationInfo($l->destination_id, $this->lang);
      if (!empty($locInfo->city)) {
        $result->locations[] = (object) array('id' => $locInfo->id, 'name' => $locInfo->city, 'lat' => $locInfo->latitude, 'long' => $locInfo->longitude);
      }
    }
    return $result;
  }



//make a result object all data of advertising array
  function getResultObject($advertising) {
    $this->ci->load->library('currconverter');
    $result = array();
    $curr = $this->ci->currconverter;
    foreach ($advertising as $t) {
      $this->set_id($t->advertising_id);
      $this->advertising_short_details();
      $adultprice = $this->adultPrice * $this->adults;
      $childprice = $this->childPrice;
      $infantprice = $this->infantPrice;
      $price = $curr->convertPrice($adultprice);
      $avgReviews = $this->advertisingReviewsAvg();
      $result[] = (object) array('id' => $this->advertisingid, 'title' => $this->title, 'slug' => base_url() . 'advertising' . $this->slug, 'thumbnail' => $this->thumbnail, 'stars' => pt_create_stars($this->stars), 'starsCount' => $this->stars, 'location' => $this->location, 'desc' => strip_tags($this->desc), 'price' => $price, 'currCode' => $curr->code, 'currSymbol' => $curr->symbol, 'inclusions' => $this->inclusions, 'avgReviews' => $avgReviews, 'latitude' => $this->latitude, 'longitude' => $this->longitude, 'advertisingDays' => $this->advertisingDays, 'advertisingNights' => $this->advertisingNights, 'advertisingType' => $this->advertisingType);
    }
    $this->currencycode = $curr->code;
    $this->currencysign = $curr->symbol;
    return $result;
  }
//make a result object limited data of advertising array
  function getLimitedResultObject($advertising) {
    $this->ci->load->library('currconverter');
    $result = array();
    $curr = $this->ci->currconverter;
    if (!empty($advertising)) {
      foreach ($advertising as $t) {
        $this->set_id($t->advertising_id);
        $this->advertising_short_details();
        $adultprice = $this->adultPrice * $this->adults;
        $childprice = $this->childPrice;
        $infantprice = $this->infantPrice;
        $avgReviews = $this->advertisingReviewsAvg();
        $price = $curr->convertPrice($adultprice);
        if (!empty($this->title)) {
          $result[] = (object) array('id' => $this->advertisingid, 'title' => $this->title, 'slug' => base_url() . 'advertising/' . $this->slug, 'thumbnail' => $this->thumbnail, 'stars' => pt_create_stars($this->stars),'starsCount' => $this->stars, 'location' => $this->location, 'price' => $price, 'currCode' => $curr->code, 'currSymbol' => $curr->symbol, 'latitude' => $this->latitude, 'longitude' => $this->longitude, 'avgReviews' => $avgReviews,);
        }
      }
    }
    return $result;
  }
//make a result object of single advertising
  function getSingleResultObject($id) {
    $this->ci->load->library('currconverter');
    $result = "";
    $curr = $this->ci->currconverter;
    if (!empty($id)) {
      $this->set_id($id);
      $this->advertising_short_details();
      $adultprice = $this->adultPrice * $this->adults;
      $childprice = $this->childPrice;
      $infantprice = $this->infantPrice;
      $avgReviews = $this->advertisingReviewsAvg();
      $price = $curr->convertPrice($adultprice);
      if (!empty($this->title)) {
        $result = (object) array('id' => $this->advertisingid, 'title' => $this->title, 'slug' => base_url() . 'advertising/' . $this->slug, 'thumbnail' => $this->thumbnail, 'stars' => pt_create_stars($this->stars), 'location' => $this->location, 'price' => $price, 'currCode' => $curr->code, 'currSymbol' => $curr->symbol, 'latitude' => $this->latitude, 'longitude' => $this->longitude, 'avgReviews' => $avgReviews,);
      }
    }
    return $result;
  }
//make a result object of booking info
  function getBookResultObject($advertisingid, $date = null, $adults = null, $child = null, $infants = null) {
    if (empty($date)) {
      $date = $this->date;
    }
    $extrasCheckUrl = base_url() . 'advertising/advertisingajaxcalls/advertisingExtrasBooking';
    $this->ci->load->library('currconverter');
    $result = array();
    $curr = $this->ci->currconverter;
//advertising details for booking page
    $this->set_id($advertisingid);
    $this->advertising_short_details();
    $extras = $this->advertisingExtras();
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
    $result["advertising"] = (object) array('id' => $this->advertisingid, 'title' => $this->title, 'slug' => base_url() . 'advertising/' . $this->slug, 'thumbnail' => $this->thumbnail, 'stars' => pt_create_stars($this->stars), 'starsCount' => $this->stars, 'location' => $this->location, 'date' => $date, 'metadesc' => $this->metadesc, 'keywords' => $this->keywords, 'extras' => $extras, 'taxAmount' => $taxAmount, 'depositAmount' => $depositAmount, 'policy' => $this->policy, 'extraChkUrl' => $extrasCheckUrl, 'adults' => $adults, 'children' => $child, 'infants' => $infants, 'advertisingDays' => $this->advertisingDays, 'advertisingNights' => $this->advertisingNights, 'currCode' => $curr->code, 'currSymbol' => $curr->symbol, 'price' => $price, 'adultprice' => $curr->convertPrice($adultPrice), 'childprice' => $curr->convertPrice($childPrice), 'infantprice' => $curr->convertPrice($infantPrice), 'subTotal' => $subTotal);
//end advertising details for booking page
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
  function getUpdatedDataBookResultObject($advertisingid, $adults = 1, $child = 0, $infant = 0, $extras) {
    $this->ci->load->library('currconverter');
    $result = array();
    $curr = $this->ci->currconverter;
    $extratotal = $this->extrasFee($extras);
    $extTotal = $extratotal['extrasTotalFee'];
    $paymethodTotal = 0; //$this->paymethodFee($this->ci->input->post('paymethod'),$total);
    $this->set_id($advertisingid);
    $this->advertising_short_details();
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
    $result = (object) array('grandTotal' => $price, 'taxAmount' => $taxAmount, 'depositAmount' => $depositAmount, 'extrashtml' => $extrasHtml, 'bookingType' => "advertising", 'currCode' => $curr->code, 'stay' => 1, 'currSymbol' => $curr->symbol, 'subitem' => $subitem, 'extrasInfo' => $extratotal);
//end advertising details for booking page
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


  public function advertisingByLocations($totalnums = 7) {
    $locData = new stdClass;
    $this->db->select('location_id,advertising_id');
    $this->db->where('position', '1');
    $this->db->group_by('location_id');
    $result = $this->db->get('pt_advertising_locations')->result();
    foreach ($result as $rs) {
      $this->db->select('advertising_id');
      $this->db->where('advertising_location', $rs->location_id);
      $this->db->where('advertising_status', 'Yes');
      $advertising = $this->db->get('pt_advertising')->result();
/*$advertisingData = $this->getSingleResultObject($rs->advertising_id);*/
      $locationInfo = pt_LocationsInfo($rs->location_id, $this->lang);
      $locData->locations[] = (object) array('id' => $rs->location_id, 'name' => $locationInfo->city, 'count' => count($advertising), 'advertising' => $advertising);
    }
    usort($locData->locations, array($this, "cmp"));
    $locData->locations = array_slice($locData->locations, 0, $totalnums);
    return $locData;
  }



  public function advertisingByDestination($totalnums = 7) {
    $locData = new stdClass;
    $this->db->select('destination_id');
    $result = $this->db->get('pt_destinations')->result();

    foreach ($result as $rs) {
      $this->db->select('advertising_id');
      $this->db->where('advertising_destination', $rs->destination_id);
      $this->db->where('advertising_status', 'Yes');
      $advertising = $this->db->get('pt_advertising')->result();
      $advertisingData = $this->getSingleResultObject($rs->advertising_id);
      $destinationInfo = pt_DestinationInfo($rs->destination_id, $this->lang);

var_dump($destinationInfo);

$locData->locations[] = (object) array('id' => $rs->destination_id, 'name' => $locationInfo->city, 'count' => count($advertising), 'advertising' => $advertising);
    }

    usort($locData->locations, array($this, "cmp"));
    $locData->locations = array_slice($locData->locations, 0, $totalnums);
    return $locData;
  }




  function cmp($a, $b) {
    return $a->count < $b->count;
  }
  public function siteMapData() {
    $advertisingData = array();
    $this->db->select('advertising_id');
    $this->db->where('advertising_status', 'Yes');
    $result = $this->db->get('pt_advertising');
    $advertising = $result->result();
    if (!empty($advertising)) {
      $advertisingData = $this->getLimitedResultObject($advertising);
    }
    return $advertisingData;
  }

  public function suggestionResults($query) {
    $response = array();
    $this->db->select('pt_advertising_translation.trans_title as title, pt_advertising.advertising_id as id,pt_advertising.advertising_title as title');
    $this->db->like('pt_advertising.advertising_title', $query);
    $this->db->or_like('pt_advertising_translation.trans_title', $query);
    $this->db->join('pt_advertising_translation', 'pt_advertising.advertising_id = pt_advertising_translation.item_id', 'left');
    $this->db->group_by('pt_advertising.advertising_id');
    $this->db->limit('25');
    $res = $this->db->get('pt_advertising')->result();
    $advertising = array();
    $locations = array();
    $this->db->select('pt_locations.id,pt_locations.location');
    $this->db->like('pt_locations.location', $query);
//$this->db->or_like('pt_locations.country',$query);
    $this->db->limit('25');
    $this->db->group_by('pt_locations.id');
    $this->db->join('pt_advertising_locations', 'pt_locations.id = pt_advertising_locations.location_id');

    $locres = $this->db->get('pt_locations')->result();
    if (!empty($locres)) {
      //$locations[] = (object) array('id' => '', 'name' => '', 'module' => 'location', 'disabled' => true);
      foreach ($locres as $l) {
        $lc++;
        $locInfo = pt_LocationsInfo($l->id, $this->lang);
        $locations[] = (object) array('id' => $l->id, 'name' => trim($locInfo->city), 'module' => 'location', 'disabled' => false);
      }
    }

/*
    $destres = $this->db->get('pt_destinations')->result();
    if (!empty($destres)) {
      //$locations[] = (object) array('id' => '', 'name' => '', 'module' => 'location', 'disabled' => true);
      foreach ($destres as $l) {
        $lc++;
        $destInfo = pt_DestinationInfo($l->id, $this->lang);
        $destination[] = (object) array('id' => $l->destination_id, 'name' => trim($destInfo->destination_title), 'module' => 'destination', 'disabled' => false);
      }
    }
*/
    if (!empty($res)) {
      $advertising[] = (object) array('id' => '', 'name' => '', 'module' => 'advertising', 'disabled' => true);
      foreach ($res as $r) {
        $title = $this->get_title($r->title, $r->id);
        $advertising[] = (object) array('id' => $r->id, 'name' => trim($title), 'module' => 'advertising', 'disabled' => false);
      }
    }
    $response = array_merge($advertising, $locations);
    $dataResponse = array("items" => $response);
    return $dataResponse;
  }


  function getLatestAdvertisingForAPI() {
    $this->ci->db->select('advertising_id,advertising_created_at');
    $this->ci->db->order_by('advertising_created_at', 'desc');
    $this->ci->db->limit('10');
    $items = $this->ci->db->get('pt_advertising')->result();
    $this->ci->load->library('currconverter');
    $result = array();
    $curr = $this->ci->currconverter;
    if (!empty($items)) {
      foreach ($items as $h) {
        $this->set_id($h->advertising_id);
        $this->advertising_short_details();
        $adultprice = $this->adultPrice * $this->adults;
        $price = $curr->convertPrice($adultprice);
        $avgReviews = $this->advertisingReviewsAvg();
        if (!empty($this->title)) {
          $result[] = (object) array('id' => $h->advertising_id, 'title' => $this->title, 'thumbnail' => $this->thumbnail, 'starsCount' => $this->stars,'location' => $this->location, 'price' => $price, 'currCode' => $curr->code, 'currSymbol' => $curr->symbol, 'avgReviews' => $avgReviews, 'createdAt' => $this->createdAt, 'module' => 'advertising');
        }
      }
    }
    return $result;
  }
}
