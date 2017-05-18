<?php

class Wedding_lib
{
    /**
     * Protected variables
     */
    protected $ci = NULL; //codeigniter instance
    protected $db; //database instatnce instance
    public $appSettings;
    public $weddingid;
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
    public $weddingNights;
    public $weddingDays;
    public $weddingType;
    public $adults;
    public $child;
    public $infants;
    public $selectedLocation;
    public $selectedWeddingType;
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
    //Related//
    //public $relatedHotels;
    //public $relatedActivity;
    //public $relatedRestaurant;
    //public $relatedWedding;
    //public $relatedTour;
    //public $relatedSpa;
    //public $relatedCar;

    function __construct()
    {

//get the CI instance
        $this->ci = &get_instance();
        $this->db = $this->ci->db;
        $this->appSettings = $this->ci->Settings_model->get_settings_data();
        $lang = $this->ci->session->userdata('set_lang');
        $defaultlang = pt_get_default_language();
        $this->ci->load->model('Wedding/Wedding_model');
        $this->ci->load->helper('Wedding/wedding_front');
        if (empty($lang)) {
            $this->lang = $defaultlang;
        } else {
            $this->lang = $lang;
        }
        $this->error = false;
        $this->errorCode = "";
        $this->date = $this->ci->input->get('date');
        $typeid = $this->ci->input->get('type');
        $this->selectedWeddingType = $this->selectedWeddingType($typeid);
        $selectedAdults = $this->ci->input->get('adults');
        $selecteChildren = $this->ci->input->get('child');
        $selectedInfants = $this->ci->input->get('infant');
        $loc = $this->ci->input->get('location');
        if (!empty($selectedAdults)) {
            $this->adults = $selectedAdults;
        } else {
            $this->adults = PT_DEFAULT_ADULTS_COUNT;
        }
        if (!empty($selecteChildren)) {
            $this->child = $selecteChildren;
        } else {
            $this->child = 0;
        }
        if (!empty($selectedInfants)) {
            $this->infants = $selectedInfants;
        } else {
            $this->infants = 0;
        }
        if (!empty($loc)) {
            $this->selectedLocation = $loc;
        } else {
            $this->selectedLocation = "";
        }
        if (empty($this->date)) {
            $this->date = date($this->appSettings[0]->date_f, strtotime('+' . CHECKIN_SPAN . ' day', time()));
        }
        $this->guestCount = $this->adults + $this->child + $this->infants;
        $getVariables = $this->ci->input->get();
        if (!empty($getVariables)) {
            $this->urlVars = "?date=" . $this->date . "&adults=" . $this->adults;
        } else {
            $this->urlVars = "";
        }
        $this->langdef = DEFLANG;
    }

    function set_weddingid($weddinglug)
    {
        $this->db->select('wedding_id');
        $this->db->where('wedding_slug', $weddinglug);
        $r = $this->db->get('pt_wedding')->result();
        $this->weddingid = $r[0]->wedding_id;
    }

    function set_lang($lang)
    {
        if (empty($lang)) {
            $defaultlang = pt_get_default_language();
            $this->lang = $defaultlang;
        } else {
            $this->lang = $lang;
        }
    }

//set wedding id by id
    function set_id($id, $currsign = null, $currcode = null)
    {
        $this->weddingid = $id;
        $this->currencysign = $currsign;
        $this->currencycode = $currcode;
    }

    function get_id()
    {
        return $this->weddingid;
    }

    function settings()
    {
        return $this->ci->Settings_model->get_front_settings('wedding');
    }

    function wishListInfo($id)
    {
        $this->wedding_short_details($id);
        $title = $this->title;
        $slug = base_url() . 'wedding/' . $this->slug;
        $thumbnail = $this->thumbnail;
        $location = $this->location;
        $stars = pt_create_stars($this->stars);
        $res = array("title" => $title, "slug" => $slug, "thumbnail" => $thumbnail, "location" => $location->city, "stars" => $stars,);
        return $res;
    }

    function selectedWeddingType($id)
    {
        $option = "";
        if (!empty($id)) {
            $res = $this->weddingTypeSettings($id);
            if (!empty($res->name)) {
                $option = "<option value=" . $res->id . " selected >" . $res->name . "</option>";
            }
        }
        return $option;
    }

    function show_wedding($offset = null)
    {
        $totalSegments = $this->ci->uri->total_segments();
        $data = array();
        $settings = $this->settings();
        $perpage = $settings[0]->front_listings;
        $sortby = $this->ci->input->get('sortby');
        if (!empty($sortby)) {
            $orderby = $sortby;
        } else {
            $orderby = $settings[0]->front_listings_order;
        }
        $priceRange = $this->priceRange($this->ci->input->get('price'));
        $rh = $this->ci->Wedding_model->list_wedding_front($priceRange);
        $wedding = $this->ci->Wedding_model->list_wedding_front($priceRange, $perpage, $offset, $orderby);
        $data['all_wedding'] = $this->getResultObject($wedding['all']);
        $data['paginationinfo'] = array('base' => 'wedding/listing', 'totalrows' => $rh['rows'], 'perpage' => $perpage, 'urisegment' => $totalSegments);
        return $data;
    }

    function showWeddingByLocation($locs, $offset = null)
    {
        $data = array();
        $settings = $this->settings();
        $perpage = $settings[0]->front_listings;
        $sortby = $this->ci->input->get('sortby');
        if (!empty($sortby)) {
            $orderby = $sortby;
        } else {
            $orderby = $settings[0]->front_listings_order;
        }
        $priceRange = $this->priceRange($this->ci->input->get('price'));
        $rh = $this->ci->Wedding_model->showWeddingByLocation($locs->locations, $priceRange);
        $wedding = $this->ci->Wedding_model->showWeddingByLocation($locs->locations, $priceRange, $perpage, $offset, $orderby);
        $data['all_wedding'] = $this->getResultObject($wedding['all']);
        $data['paginationinfo'] = array('base' => 'wedding/' . $locs->urlBase, 'totalrows' => $rh['rows'], 'perpage' => $perpage, 'urisegment' => $locs->uriSegment);
        return $data;
    }

    function search_wedding($location, $offset = null)
    {
        $data = array();
        $settings = $this->settings();
        $perpage = $settings[0]->front_search;
        $orderby = $settings[0]->front_search_order;
        $totalSegments = $this->ci->uri->total_segments();
        if ($totalSegments < 5) {
            $location = "";
            $segments = "";
            $urisegment = 3;
        } else {
            $segments = '/' . $this->ci->uri->segment(3) . '/' . $this->ci->uri->segment(4) . '/' . $this->ci->uri->segment(5);
            $urisegment = 6;
        }
        $priceRange = $this->priceRange($this->ci->input->get('price'));
        $rh = $this->ci->Wedding_model->search_wedding_front($location, $priceRange);
        $wedding = $this->ci->Wedding_model->search_wedding_front($location, $priceRange, $perpage, $offset, $orderby);
        $data['all_wedding'] = $this->getResultObject($wedding['all']);
        $data['paginationinfo'] = array('base' => 'wedding/search' . $segments, 'totalrows' => $rh['rows'], 'perpage' => $perpage, 'urisegment' => $urisegment);
        return $data;
    }

    function wedding_details($weddingid = null, $date = null)
    {
        if (empty($weddingid)) {
            $weddingid = $this->weddingid;
        } else {
            $weddingid = $weddingid;
        }
        $this->ci->load->library('currconverter');
        $curr = $this->ci->currconverter;
        if (!empty($date)) {
            $this->date = $date;
        }
        $this->db->where('wedding_id', $weddingid);
        $details = $this->db->get('pt_wedding')->result();
        $title = $this->get_title($details[0]->wedding_title, $details[0]->wedding_id);
        $stars = $details[0]->wedding_stars;
        $desc = $this->get_description($details[0]->wedding_desc, $details[0]->wedding_id);
        $policy = $this->get_policy($details[0]->wedding_privacy, $details[0]->wedding_id);
        $locationInfoUrl = pt_LocationsInfo($details[0]->wedding_location);
        $countryName = url_title($locationInfoUrl->country, 'dash', true);
        $cityName = url_title($locationInfoUrl->city, 'dash', true);
        $slug = $countryName . '/' . $cityName . '/' . $details[0]->wedding_slug . $this->urlVars;
        $bookingSlug = $details[0]->wedding_slug . $this->urlVars;
        $keywords = $this->get_keywords($details[0]->wedding_meta_keywords, $details[0]->wedding_id);
        $metadesc = $this->get_metaDesc($details[0]->wedding_meta_desc, $details[0]->wedding_id);
        $weddingDays = $details[0]->wedding_days;
        $weddingNights = $details[0]->wedding_nights;
        if (!empty($details[0]->wedding_amenities)) {
            $weddingAmenities = explode(",", $details[0]->wedding_amenities);
            foreach ($weddingAmenities as $tm) {
                $amts[] = $this->weddingTypeSettings($tm);
            }
        } else {
            $amts = array();
        }
        $inclusions = $amts;
        if (!empty($details[0]->wedding_exclusions)) {
            $weddingExclusions = explode(",", $details[0]->wedding_exclusions);
            foreach ($weddingExclusions as $exc) {
                $excs[] = $this->weddingTypeSettings($exc);
            }
        } else {
            $excs = array();
        }
        $exclusions = $excs;
        if (!empty($details[0]->wedding_payment_opt)) {
            $weddingPaymentOpts = explode(",", $details[0]->wedding_payment_opt);
            foreach ($weddingPaymentOpts as $p) {
                $payopts[] = $this->weddingTypeSettings($p);
            }
        } else {
            $payopts = array();
        }
        $paymentOptions = $payopts;
        if (!empty($details[0]->wedding_related)) {
            $rwedding = explode(",", $details[0]->wedding_related);
        } else {
            $rwedding = "";
        }
        if (!empty($details[0]->product_related_activity)) {
          $ractivity = explode(",", $details[0]->product_related_activity);
        }
        else {
          $ractivity = "";
        }
        if (!empty($details[0]->product_related_restaurant)) {
          $rrestaurant = explode(",", $details[0]->product_related_restaurant);
        }
        else {
          $rrestaurant = "";
        }
        if (!empty($details[0]->product_related_tours)) {
          $rtour = explode(",", $details[0]->product_related_tours);
        }
        else {
          $rtour = "";
        }
        if (!empty($details[0]->product_related_spa)) {
          $rspa = explode(",", $details[0]->product_related_spa);
        }
        else {
          $rspa = "";
        }
        if (!empty($details[0]->product_related_cars)) {
          $rcar = explode(",", $details[0]->product_related_cars);
        }
        else {
          $rcar = "";
        }

        $relatedWedding = $this->getRelatedWedding($rwedding);
        $relatedActivity = $this->ci->Activity_lib->getRelatedActivity($ractivity);
        $relatedRestaurant = $this->ci->Restaurant_lib->getRelatedRestaurant($rrestaurant);
        $relatedTour = $this->ci->Tours_lib->getRelatedTours($rtour);
        $relatedSpa = $this->ci->Spa_lib->getRelatedSpa($rspa);
        $relatedCar = $this->ci->Cars_lib->getRelatedCars($rcar);
        $thumbnail = PT_WEDDING_SLIDER_THUMB . $details[0]->thumbnail_image;
        $city = pt_LocationsInfo($details[0]->wedding_location, $this->lang);
        $location = $city->city; // $details[0]->wedding_location;
//	$isfeatured = $this->is_featured();
        $website = $details[0]->wedding_website;
        $phone = $details[0]->wedding_phone;
        $email = $details[0]->wedding_email;
        $taxcom = $this->wedding_tax_commision();
        $comm_type = $taxcom['commtype'];
        $comm_value = $taxcom['commval'];
        $tax_type = $taxcom['taxtype'];
        $tax_value = $taxcom['taxval'];
        $latitude = $details[0]->wedding_latitude;
        $longitude = $details[0]->wedding_longitude;
        $totalAdutlsPrice = $details[0]->wedding_adult_price * $this->adults;
        $totalChildPrice = $details[0]->wedding_child_price * $this->child;
        $totalInfantsPrice = $details[0]->wedding_infant_price * $this->infants;
        $adultPrice = $curr->convertPrice($totalAdutlsPrice);
        $childPrice = $curr->convertPrice($totalChildPrice);
        $infantPrice = $curr->convertPrice($totalInfantsPrice);
        $perAdultPrice = $curr->convertPrice($details[0]->wedding_adult_price);
        $perChildPrice = $curr->convertPrice($details[0]->wedding_child_price);
        $perInfantPrice = $curr->convertPrice($details[0]->wedding_infant_price);
        $maxAdults = $details[0]->wedding_max_adults;
        $maxChild = $details[0]->wedding_max_child;
        $maxInfant = $details[0]->wedding_max_infant;
        $this->checkErrors($maxAdults, $maxChild, $maxInfant);
        $adultStatus = $details[0]->adult_status;
        $childStatus = $details[0]->child_status;
        $infantStatus = $details[0]->infant_status;
        $sliderImages = $this->weddingImages($details[0]->wedding_id);
        $ogimg = $this->ogimg($details[0]->activity_id);
        $totalCost = $totalAdutlsPrice + $totalChildPrice + $totalInfantsPrice;
        $taxcom = $this->wedding_tax_commision($details[0]->wedding_id);
        $this->comm_type = $taxcom['commtype'];
        $this->comm_value = $taxcom['commval'];
        $this->tax_type = $taxcom['taxtype'];
        $this->tax_value = $taxcom['taxval'];
        $this->setDeposit($curr->convertPriceFloat($totalCost, 2));
        $depositAmount = $this->deposit;
        $detailResults = (object)array(
          'id' => $details[0]->wedding_id,
          'title' => $title,
          'slug' => $slug,
          'bookingSlug' => $bookingSlug,
          'thumbnail' => $thumbnail,
          'stars' => pt_create_stars($stars),
          'starsCount' => $stars,
          'location' => $location,
          'desc' => $desc,
          'inclusions' => $inclusions,
          'exclusions' => $exclusions,
          'latitude' => $latitude,
          'longitude' => $longitude,
          'sliderImages' => $sliderImages,
          'relatedItems' => $relatedWedding,
          'relatedActivity' => $relatedActivity,
          'relatedRestaurant' => $relatedRestaurant,
          'relatedTour' => $relatedTour,
          'relatedSpa' => $relatedSpa,
          'relatedCar' => $relatedCar,
          'paymentOptions' => $paymentOptions,
          'metadesc' => $metadesc,
          'keywords' => $keywords,
          'policy' => $policy,
          'website' => $website,
          'email' => $email,
          'phone' => $phone,
          'maxAdults' => $maxAdults,
          'maxChild' => $maxChild,
          'maxInfant' => $maxInfant,
          'adultStatus' => $adultStatus,
          'childStatus' => $childStatus,
          'infantStatus' => $infantStatus,
          'adultPrice' => $adultPrice,
          'childPrice' => $childPrice,
          'infantPrice' => $infantPrice,
          'perAdultPrice' => $perAdultPrice,
          'perChildPrice' => $perChildPrice,
          'perInfantPrice' => $perInfantPrice,
          'currCode' => $curr->code,
          'currSymbol' => $curr->symbol,
          'date' => $this->date,
          'totalCost' => $curr->convertPrice($totalCost),
          'comType' => $comm_type,
          'comValue' => $comm_value,
          'taxType' => $tax_type,
          'taxValue' => $tax_value,
          'weddingDays' => $weddingDays,
          'weddingNights' => $weddingNights,
          'totalDeposit' => $depositAmount,
          'mapAddress' => $details[0]->wedding_mapaddress,
          'ogimg' => $ogimg);
        return $detailResults;
    }

    function wedding_short_details($weddingid)
    {
        if (empty($weddingid)) {
            $weddingid = $this->weddingid;
        }
        $this->db->select('wedding_title,wedding_stars,wedding_slug,wedding_desc,wedding_privacy,wedding_max_adults,wedding_max_child,
   wedding_max_infant,wedding_basic_price,wedding_basic_discount,wedding_adult_price,wedding_child_price,wedding_infant_price,wedding_amenities,wedding_exclusions,wedding_days,wedding_nights,thumbnail_image,wedding_location,wedding_latitude,wedding_longitude,wedding_type,wedding_created_at');
        $this->db->where('wedding_id', $weddingid);
        $details = $this->db->get('pt_wedding')->result();
        $this->stars = $details[0]->wedding_stars;
        $this->title = $this->get_title($details[0]->wedding_title);
        $this->desc = $this->get_description($details[0]->wedding_desc);
        $this->policy = $this->get_policy($details[0]->wedding_privacy);
        $this->weddingDays = $details[0]->wedding_days;
        $this->weddingNights = $details[0]->wedding_nights;
        $this->weddingNights = $details[0]->wedding_nights;
        $this->createdAt = $details[0]->wedding_created_at;
        $maxAdults = $details[0]->wedding_max_adults;
        $maxChild = $details[0]->wedding_max_child;
        $maxInfant = $details[0]->wedding_max_infant;
        $this->checkErrors($maxAdults, $maxChild, $maxInfant);
//get country and city name for url slug
        $locationInfoUrl = pt_LocationsInfo($details[0]->wedding_location);
        $countryName = url_title($locationInfoUrl->country, 'dash', true);
        $cityName = url_title($locationInfoUrl->city, 'dash', true);
        $this->slug = $countryName . '/' . $cityName . '/' . $details[0]->wedding_slug . $this->urlVars;
        $this->bookingSlug = $details[0]->wedding_slug . $this->urlVars;
        $city = pt_LocationsInfo($details[0]->wedding_location, $this->lang);
        $this->location = $city->city;
//$details[0]->wedding_location;
        $this->latitude = $details[0]->wedding_latitude;
        $this->longitude = $details[0]->wedding_longitude;
        $this->thumbnail = PT_WEDDING_SLIDER_THUMB . $details[0]->thumbnail_image;
        $type = $this->weddingTypeSettings($details[0]->wedding_type);
        $this->weddingType = $type->name;
        $taxcom = $this->wedding_tax_commision();
        $this->comm_type = $taxcom['commtype'];
        $this->comm_value = $taxcom['commval'];
        $this->tax_type = $taxcom['taxtype'];
        $this->tax_value = $taxcom['taxval'];
        $this->adultPrice = $details[0]->wedding_adult_price;
        $this->childPrice = $details[0]->wedding_child_price;
        $this->infantPrice = $details[0]->wedding_infant_price;
        $this->isfeatured = $this->is_featured();
        return $details;
    }

    function wedding_tax_commision($weddingid = null)
    {
        if (empty($weddingid)) {
            $weddingid = $this->weddingid;
        }
        $res = array();
        $this->db->select('wedding_comm_fixed,wedding_comm_percentage,wedding_tax_fixed,wedding_tax_percentage');
        $this->db->where('wedding_id', $weddingid);
        $result = $this->db->get('pt_wedding')->result();
        $commfixed = $result[0]->wedding_comm_fixed;
        $commper = $result[0]->wedding_comm_percentage;
        $taxfixed = $result[0]->wedding_tax_fixed;
        $taxper = $result[0]->wedding_tax_percentage;
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

// get wedding images
    function weddingImages($weddingid)
    {
        if (empty($weddingid)) {
            $weddingid = $this->weddingid;
        }
        $this->db->where('timg_wedding_id', $weddingid);
        $this->db->where('timg_approved', '1');
        $this->db->order_by('timg_order', 'asc');
        $res = $this->db->get('pt_wedding_images')->result();
        if (empty($res)) {
            $result[] = array("fullImage" => PT_WEDDING_SLIDER_THUMB . PT_BLANK_IMG, "thumbImage" => PT_WEDDING_SLIDER_THUMB . PT_BLANK_IMG);
        } else {
            foreach ($res as $r) {
                $result[] = array("fullImage" => PT_WEDDING_SLIDER . $r->timg_image, "thumbImage" => PT_WEDDING_SLIDER_THUMB . $r->timg_image);
            }
        }
        return $result;
    }

    function getFeaturedWedding()
    {
        $wedding = $this->featured_wedding_list();
        $result = $this->getResultObject($wedding);
        return $result;
    }

    function getLocationBasedFeaturedWedding($loc)
    {
        $settings = $this->settings();
        $limit = $settings[0]->front_homepage;
        $this->db->select('wedding_id');
        $this->db->where('wedding_location', $loc);
        $this->db->where('wedding_status', 'Yes');
        $weddingList = $this->db->get('pt_wedding')->result();
        $wedding = array();
        foreach ($weddingList as $t) {
            $isFeatured = $this->is_featured($t->wedding_id);
            if ($isFeatured) {
                $wedding[] = (object)array('wedding_id' => $t->wedding_id);
            }
        }
        $wedding = array_slice($wedding, 0, $limit);
        $result = $this->getResultObject($wedding);
        return $result;
    }

    function getTopRatedWedding()
    {
        $wedding = $this->ci->Wedding_model->popular_wedding_front();
        $result = $this->getResultObject($wedding);
        return $result;
    }

    function getRelatedWedding($wedding)
    {
        $resultwedding = array();
        $result = array();
        $settings = $this->settings();
        $limit = $settings[0]->front_related;
        $count = 0;
        if (!empty($wedding)) {
            foreach ($wedding as $t) {
                $count++;
                if ($count <= $limit) {
                    $resultwedding[] = (object)array('wedding_id' => $t);
                }

            }
        }
        $result = $this->getLimitedResultObject($resultwedding);
        return $result;
    }

// Get Wedding updated Price on changing adults, child and infant count.
    function updatedPrice($weddingid, $adults = 1, $child = 0, $infant = 0)
    {
        $this->ci->load->library('currconverter');
        $curr = $this->ci->currconverter;
        $this->db->select('wedding_adult_price,wedding_child_price,wedding_infant_price');
        $this->db->where('wedding_id', $weddingid);
        $details = $this->db->get('pt_wedding')->result();
        $totalAdutlsPrice = $details[0]->wedding_adult_price * $adults;
        $totalChildPrice = $details[0]->wedding_child_price * $child;
        $totalInfantsPrice = $details[0]->wedding_infant_price * $infant;
        $adultPrice = $curr->convertPrice($totalAdutlsPrice);
        $childPrice = $curr->convertPrice($totalChildPrice);
        $infantPrice = $curr->convertPrice($totalInfantsPrice);
        $totalCost = $totalAdutlsPrice + $totalChildPrice + $totalInfantsPrice;
        $taxcom = $this->wedding_tax_commision($weddingid);
        $this->comm_type = $taxcom['commtype'];
        $this->comm_value = $taxcom['commval'];
        $this->tax_type = $taxcom['taxtype'];
        $this->tax_value = $taxcom['taxval'];
        $this->setDeposit($totalCost);
        $depositAmount = $this->deposit;
        if (!empty($curr->symbol)) {
            $currSymbol = $curr->symbol;
        } else {
            $currSymbol = "";
        }
        $detailResults = array('id' => $weddingid, 'adultPrice' => $adultPrice, 'childPrice' => $childPrice, 'infantPrice' => $infantPrice, 'currCode' => $curr->code, 'currSymbol' => $currSymbol, 'totalDeposit' => $curr->convertPrice($depositAmount), 'totalCost' => $curr->convertPrice($totalCost));
        return json_encode($detailResults);
    }

    function ogimg($weddingid)
    {
        if (empty($weddingid)) {
            $weddingid = $this->weddingid;
        }
        $this->db->where('timg_wedding_id', $weddingid);
        $this->db->where('timg_order', '1');
        $res = $this->db->get('pt_wedding_images')->result();
        if (empty($res)) {
            $result = 'uploads/global/favicon.png';
        } else {
            $result =  'uploads/images/weddings/slider/thumbs/'.$res[0]->timg_image;
        }
        return $result;
    }

    function get_thumbnail()
    {
        $res = $this->ci->Wedding_model->default_wedding_img($this->weddingid);
        if (!empty($res)) {
            return PT_WEDDING_SLIDER_THUMB . $res;
        } else {
            return PT_BLANK;
        }
    }

    function get_title($deftitle, $weddingid)
    {
        if (empty($weddingid)) {
            $weddingid = $this->weddingid;
        }
        if ($this->lang == $this->langdef) {
            $title = $deftitle;
        } else {
            $this->db->where('item_id', $weddingid);
            $this->db->where('trans_lang', $this->lang);
            $res = $this->db->get('pt_wedding_translation')->result();
            $title = $res[0]->trans_title;
            if (empty($title)) {
                $title = $deftitle;
            }
        }
        return $title;
    }

    function get_description($defdesc, $weddingid)
    {
        if (empty($weddingid)) {
            $weddingid = $this->weddingid;
        }
        if ($this->lang == $this->langdef) {
            $desc = $defdesc;
        } else {
            $this->db->where('item_id', $weddingid);
            $this->db->where('trans_lang', $this->lang);
            $res = $this->db->get('pt_wedding_translation')->result();
            $desc = $res[0]->trans_desc;
            if (empty($desc)) {
                $desc = $defdesc;
            }
        }
        return $desc;
    }

    function get_policy($defpolicy, $weddingid)
    {
        if (empty($weddingid)) {
            $weddingid = $this->weddingid;
        }
        if ($this->lang == $this->langdef) {
            $policy = $defpolicy;
        } else {
            $this->db->where('item_id', $weddingid);
            $this->db->where('trans_lang', $this->lang);
            $res = $this->db->get('pt_wedding_translation')->result();
            $policy = $res[0]->trans_policy;
            if (empty($policy)) {
                $policy = $defpolicy;
            }
        }
        return $policy;
    }

    function get_keywords($defkeywords, $weddingid = null)
    {
        if (empty($weddingid)) {
            $weddingid = $this->weddingid;
        }
        if ($this->lang == $this->langdef) {
            $keywords = $defkeywords;
        } else {
            $this->db->where('item_id', $weddingid);
            $this->db->where('trans_lang', $this->lang);
            $res = $this->db->get('pt_wedding_translation')->result();
            $keywords = $res[0]->metakeywords;
            if (empty($keywords)) {
                $keywords = $defkeywords;
            }
        }
        return $keywords;
    }

    function get_metaDesc($defmeta, $weddingid = null)
    {
        if (empty($weddingid)) {
            $weddingid = $this->weddingid;
        }
        if ($this->lang == $this->langdef) {
            $meta = $defmeta;
        } else {
            $this->db->where('item_id', $weddingid);
            $this->db->where('trans_lang', $this->lang);
            $res = $this->db->get('pt_wedding_translation')->result();
            $meta = $res[0]->metadesc;
            if (empty($meta)) {
                $meta = $defmeta;
            }
        }
        return $meta;
    }

    function get_metaImg($defimg, $weddingid = null)
    {
        if (empty($weddingid)) {
            $weddingid = $this->weddingid;
        }
        if ($this->lang == $this->langdef) {
            $meta = $defimg;
        } else {
            $this->db->where('timg_wedding_id', $weddingid);
            $this->db->where('timg_order', '1');
            $res = $this->db->get('pt_wedding_images')->result();
            $meta = $res[0]->timg_image;
            if (empty($meta)) {
                $meta = $defimg;
            }
        }
        return $meta;
    }

    function weddingExtras($weddingid = null)
    {
        if (empty($weddingid)) {
            $weddingid = $this->weddingid;
        }
        $today = time();
        $result = array();
//	$this->db->where('extras_from  <=', $today);
//	$this->db->where('extras_to >=', $today);
        $this->db->where('extras_module', 'wedding');
//  $this->db->or_where('extras_forever','forever');
        $this->db->order_by('extras_id', 'desc');
        $this->db->like('extras_for', $weddingid, 'both');
        $this->db->having('extras_status', 'Yes');
        $ext = $this->db->get('pt_extras')->result();
        $this->ci->load->library('currconverter');
        $curr = $this->ci->currconverter;
        if (!empty($ext)) {
            foreach ($ext as $e) {
                $trans = $this->extrasTranslation($e->extras_id, $e->extras_title, $e->extras_desc);
                $price = $curr->convertPrice($e->extras_basic_price);
                $result[] = (object)array("id" => $e->extras_id, "extraTitle" => $trans['title'], "extraDesc" => $trans['desc'], 'extraPrice' => $price, 'thumbnail' => PT_WEDDING_EXTRAS_IMAGES . $e->extras_image);
            }
        }
        return $result;
    }

    function getLocationsList()
    {
        $resultLocations = array();
        $this->db->select('location_id');
        $this->db->group_by('location_id');
        $locations = $this->db->get('pt_wedding_locations')->result();
        foreach ($locations as $loc) {
            $locInfo = pt_LocationsInfo($loc->location_id, $this->lang);
            if (!empty($locInfo->city)) {
                $resultLocations[] = (object)array('id' => $locInfo->id, 'name' => $locInfo->city);
            }
        }
        return $resultLocations;
    }

    function extras_translation($id)
    {
        $language = $this->lang;
        $result = array();
        $this->db->select('extras_title,extras_desc');
        $this->db->where('extras_id', $id);
        $re = $this->db->get('pt_extras')->result();
        if ($language == $this->langdef) {
            $result['title'] = $re[0]->extras_title;
            $result['desc'] = $re[0]->extras_desc;
        } else {
            $this->db->select('trans_title,trans_desc');
            $this->db->where('trans_extras_id', $id);
            $this->db->where('trans_lang', $language);
            $r = $this->db->get('pt_extras_translation')->result();
            if (empty($r[0]->trans_title)) {
                $result['title'] = $re[0]->extras_title;
            } else {
                $result['title'] = $r[0]->trans_title;
            }
            if (empty($r[0]->trans_desc)) {
                $result['desc'] = $re[0]->extras_desc;
            } else {
                $result['desc'] = $r[0]->trans_desc;
            }
        }
        return $result;
    }

// wedding Reviews
    function wedding_reviews($weddingid)
    {
        if (empty($weddingid)) {
            $weddingid = $this->weddingid;
        }
        $this->db->where('review_status', 'Yes');
        $this->db->where('review_module', 'wedding');
        $this->db->where('review_itemid', $weddingid);
        $this->db->order_by('review_id', 'desc');
        return $this->db->get('pt_reviews')->result();
    }

// wedding Reviews for API
    function wedding_reviews_for_api($weddingid)
    {
        if (empty($weddingid)) {
            $weddingid = $this->weddingid;
        }
        $result = array();
        $this->db->select('review_overall as rating,review_name as review_by,review_comment,review_date');
        $this->db->where('review_status', 'Yes');
        $this->db->where('review_module', 'wedding');
        $this->db->where('review_itemid', $weddingid);
        $this->db->order_by('review_id', 'desc');
        $rs = $this->db->get('pt_reviews')->result();
        foreach ($rs as $r) {
            $result[] = array("rating" => $r->rating, "review_by" => $r->review_by, "review_comment" => $r->review_comment, "review_date" => pt_show_date_php($r->review_date));
        }
        return $result;
    }

// wedding  Reviews Averages
    function weddingReviewsAvg($weddingid)
    {
        if (empty($weddingid)) {
            $weddingid = $this->weddingid;
        }
        $this->db->select("COUNT(*) AS totalreviews");
        $this->db->select_avg('review_overall', 'overall');
        $this->db->select_avg('review_clean', 'clean');
        $this->db->select_avg('review_facilities', 'facilities');
        $this->db->select_avg('review_staff', 'staff');
        $this->db->select_avg('review_comfort', 'comfort');
        $this->db->select_avg('review_location', 'location');
        $this->db->where('review_status', 'Yes');
        $this->db->where('review_module', 'wedding');
        $this->db->where('review_itemid', $weddingid);
        $res = $this->db->get('pt_reviews')->result();
        $clean = round($res[0]->clean, 1);
        $comfort = round($res[0]->comfort, 1);
        $location = round($res[0]->location, 1);
        $facilities = round($res[0]->facilities, 1);
        $staff = round($res[0]->staff, 1);
        $totalreviews = $res[0]->totalreviews;
        $overall = round($res[0]->overall, 1);
        $result = (object)array('clean' => $clean, 'comfort' => $comfort, 'location' => $location, 'facilities' => $facilities, 'staff' => $staff, 'totalReviews' => $totalreviews, 'overall' => $overall);
        return $result;
    }

// Wedding visiting Cities
    function wedding_visiting_cities()
    {
        $this->db->select('map_city_name');
        $this->db->where('map_city_type', 'visit');
        $this->db->where('map_wedding_id', $this->weddingid);
        return $this->db->get('pt_wedding_maps')->result();
    }

    function translated_data($lang)
    {
        $this->db->where('item_id', $this->weddingid);
        $this->db->where('trans_lang', $lang);
        return $this->db->get('pt_wedding_translation')->result();
    }

    function is_featured($weddingid)
    {
        if (empty($weddingid)) {
            $weddingid = $this->weddingid;
        } else {
            $weddingid = $weddingid;
        }
        $this->db->select('wedding_id');
        $this->db->where('wedding_is_featured', 'yes');
        $this->db->where('wedding_featured_from <', time());
        $this->db->where('wedding_featured_to >', time());
        $this->db->or_where('wedding_featured_forever', 'forever');
        $this->db->having('wedding_id', $weddingid);
        return $this->db->get('pt_wedding')->num_rows();
    }

    function featured_wedding_list()
    {
        $settings = $this->settings();
        $limit = $settings[0]->front_homepage;
        $orderby = $settings[0]->front_homepage_order;
        $this->db->select('wedding_id,wedding_order,wedding_title,wedding_status,wedding_location');
        $this->db->where('wedding_is_featured', 'yes');
        $this->db->where('wedding_featured_from <', time());
        $this->db->where('wedding_featured_to >', time());
        $this->db->or_where('wedding_featured_forever', 'forever');
        $this->db->having('wedding_status', 'Yes');
        $this->db->limit($limit);
        if ($orderby == "za") {
            $this->db->order_by('pt_wedding.wedding_title', 'desc');
        } elseif ($orderby == "az") {
            $this->db->order_by('pt_wedding.wedding_title', 'asc');
        } elseif ($orderby == "oldf") {
            $this->db->order_by('pt_wedding.wedding_id', 'asc');
        } elseif ($orderby == "newf") {
            $this->db->order_by('pt_wedding.wedding_id', 'desc');
        } elseif ($orderby == "ol") {
            $this->db->order_by('pt_wedding.wedding_order', 'asc');
        }
        return $this->db->get('pt_wedding')->result();
    }

// wedding Reviews
    function weddingReviews($weddingid = null)
    {
        if (empty($weddingid)) {
            $weddingid = $this->weddingid;
        }
        $this->db->where('review_status', 'Yes');
        $this->db->where('review_module', 'wedding');
        $this->db->where('review_itemid', $weddingid);
        $this->db->order_by('review_id', 'desc');
        return $this->db->get('pt_reviews')->result();
    }

    function extrasTranslation($id, $title, $desc)
    {
        $language = $this->lang;
        $this->db->select('trans_title,trans_desc');
        $this->db->where('trans_extras_id', $id);
        $this->db->where('trans_lang', $language);
        $r = $this->db->get('pt_extras_translation')->result();
        if (empty($r[0]->trans_title)) {
            $result['title'] = $title;
        } else {
            $result['title'] = $r[0]->trans_title;
        }
        if (empty($r[0]->trans_desc)) {
            $result['desc'] = $desc;
        } else {
            $result['desc'] = $r[0]->trans_desc;
        }
        return $result;
    }

    function weddingTypes()
    {
        $weddingtypes = array();
        $this->db->select('sett_name,sett_id');
        $this->db->where('sett_type', 'ttypes');
        $types = $this->db->get('pt_wedding_types_settings')->result();
        foreach ($types as $t) {
            $tname = $this->weddingTypeSettings($t->sett_id);
            $weddingtypes[] = (object)array('id' => $t->sett_id, 'name' => $tname->name);
        }
        return $weddingtypes;
    }

// Wedding Type
    function weddingTypeSettings($id)
    {
        $language = $this->lang;
        $result = new stdClass;
        $this->db->select('sett_name,sett_img');
        $this->db->where('sett_id', $id);
        $this->db->where('sett_status', 'Yes');
        $re = $this->db->get('pt_wedding_types_settings')->result();
        $result->icon = PT_WEDDING_ICONS . $re[0]->sett_img;
        $result->id = $id;
        if ($language == $this->langdef) {
            $result->name = $re[0]->sett_name;
        } else {
            $this->db->select('trans_name');
            $this->db->where('sett_id', $id);
            $this->db->where('trans_lang', $language);
            $r = $this->db->get('pt_wedding_types_settings_translation')->result();
            if (empty($r[0]->trans_name)) {
                $result->name = $re[0]->sett_name;
            } else {
                $result->name = $r[0]->trans_name;
            }
        }
        return $result;
    }

//Populate Wedding Types according to the location selected
    function getWeddingTypesLocationBased($location)
    {
        $result = new stdClass;
        $result->hasResult = FALSE;
        $result->optionsList = "";
        $weddingTypes = array();
        $weddingIDs = array();
        $this->db->where('location_id', $location);
        $this->db->group_by('wedding_id');
        $wedding = $this->db->get('pt_wedding_locations')->result();
        if (!empty($wedding)) {
            foreach ($wedding as $t) {
                $weddingIDs[] = $t->wedding_id;
            }
        }
        $this->db->select('wedding_type');
//$this->db->where('wedding_location',$location);
        if (!empty($weddingIDs)) {
            $this->db->where_in('wedding_id', $weddingIDs);
        } else {
            $this->db->where('wedding_id', '0');
        }
        $this->db->group_by('wedding_type');
        $res = $this->db->get('pt_wedding')->result();
        if (!empty($res)) {
            foreach ($res as $r) {
                $weddingTypes[] = $r->wedding_type;
            }
            $result->hasResult = TRUE;
            foreach ($weddingTypes as $type) {
                $typeDetails = $this->weddingTypeSettings($type);
                $result->optionsList .= "<option value='" . $typeDetails->id . "' selected>" . $typeDetails->name . "</option>";
                $result->types[] = array("id" => $typeDetails->id, "name" => $typeDetails->name);
            }
        } else {
            $result->hasResult = FALSE;
            $result->optionsList = "<option value='' selected> Select </option>";
        }
        return $result;
    }

    function weddingLocations($id = null)
    {
        $result = new stdClass;
        if (empty($id)) {
            $id = $this->weddingid;
        }
        $this->db->where('wedding_id', $id);
        $locs = $this->db->get('pt_wedding_locations')->result();
        foreach ($locs as $l) {
            $locInfo = pt_LocationsInfo($l->location_id, $this->lang);
            if (!empty($locInfo->city)) {
                $result->locations[] = (object)array('id' => $locInfo->id, 'name' => $locInfo->city, 'lat' => $locInfo->latitude, 'long' => $locInfo->longitude);
            }
        }
        return $result;
    }

//make a result object all data of wedding array
    function getResultObject($wedding)
    {
        $this->ci->load->library('currconverter');
        $result = array();
        $curr = $this->ci->currconverter;
        foreach ($wedding as $t) {
            $this->set_id($t->wedding_id);
            $this->wedding_short_details();
            $adultprice = $this->adultPrice * $this->adults;
            $childprice = $this->childPrice;
            $infantprice = $this->infantPrice;
            $price = $curr->convertPrice($adultprice);
            $avgReviews = $this->weddingReviewsAvg();
            $result[] = (object)array('id' => $this->weddingid, 'title' => $this->title, 'slug' => base_url() . 'wedding/' . $this->slug, 'thumbnail' => $this->thumbnail, 'stars' => pt_create_stars($this->stars), 'starsCount' => $this->stars, 'location' => $this->location, 'desc' => strip_tags($this->desc), 'price' => $price, 'currCode' => $curr->code, 'currSymbol' => $curr->symbol, 'inclusions' => $this->inclusions, 'avgReviews' => $avgReviews, 'latitude' => $this->latitude, 'longitude' => $this->longitude, 'weddingDays' => $this->weddingDays, 'weddingNights' => $this->weddingNights, 'weddingType' => $this->weddingType);
        }
        $this->currencycode = $curr->code;
        $this->currencysign = $curr->symbol;
        return $result;
    }

//make a result object limited data of wedding array
    function getLimitedResultObject($wedding)
    {
        $this->ci->load->library('currconverter');
        $result = array();
        $curr = $this->ci->currconverter;
        if (!empty($wedding)) {
            foreach ($wedding as $t) {
                $this->set_id($t->wedding_id);
                $this->wedding_short_details();
                $adultprice = $this->adultPrice * $this->adults;
                $childprice = $this->childPrice;
                $infantprice = $this->infantPrice;
                $avgReviews = $this->weddingReviewsAvg();
                $price = $curr->convertPrice($adultprice);
                if (!empty($this->title)) {
                    $result[] = (object)array('id' => $this->weddingid, 'title' => $this->title, 'slug' => base_url() . 'wedding/' . $this->slug, 'thumbnail' => $this->thumbnail, 'stars' => pt_create_stars($this->stars), 'starsCount' => $this->stars, 'location' => $this->location, 'price' => $price, 'currCode' => $curr->code, 'currSymbol' => $curr->symbol, 'latitude' => $this->latitude, 'longitude' => $this->longitude, 'avgReviews' => $avgReviews,);
                }
            }
        }
        return $result;
    }

//make a result object of single wedding
    function getSingleResultObject($id)
    {
        $this->ci->load->library('currconverter');
        $result = "";
        $curr = $this->ci->currconverter;
        if (!empty($id)) {
            $this->set_id($id);
            $this->wedding_short_details();
            $adultprice = $this->adultPrice * $this->adults;
            $childprice = $this->childPrice;
            $infantprice = $this->infantPrice;
            $avgReviews = $this->weddingReviewsAvg();
            $price = $curr->convertPrice($adultprice);
            if (!empty($this->title)) {
                $result = (object)array('id' => $this->weddingid, 'title' => $this->title, 'slug' => base_url() . 'wedding/' . $this->slug, 'thumbnail' => $this->thumbnail, 'stars' => pt_create_stars($this->stars), 'location' => $this->location, 'price' => $price, 'currCode' => $curr->code, 'currSymbol' => $curr->symbol, 'latitude' => $this->latitude, 'longitude' => $this->longitude, 'avgReviews' => $avgReviews,);
            }
        }
        return $result;
    }

//make a result object of booking info
    function getBookResultObject($weddingid, $date = null, $adults = null, $child = null, $infants = null)
    {
        if (empty($date)) {
            $date = $this->date;
        }
        $extrasCheckUrl = base_url() . 'wedding/weddingajaxcalls/weddingExtrasBooking';
        $this->ci->load->library('currconverter');
        $result = array();
        $curr = $this->ci->currconverter;
//wedding details for booking page
        $this->set_id($weddingid);
        $this->wedding_short_details();
        $extras = $this->weddingExtras();
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
        $result["wedding"] = (object)array('id' => $this->weddingid, 'title' => $this->title, 'slug' => base_url() . 'wedding/' . $this->slug, 'thumbnail' => $this->thumbnail, 'stars' => pt_create_stars($this->stars), 'starsCount' => $this->stars, 'location' => $this->location, 'date' => $date, 'metadesc' => $this->metadesc, 'keywords' => $this->keywords, 'extras' => $extras, 'taxAmount' => $taxAmount, 'depositAmount' => $depositAmount, 'policy' => $this->policy, 'extraChkUrl' => $extrasCheckUrl, 'adults' => $adults, 'children' => $child, 'infants' => $infants, 'weddingDays' => $this->weddingDays, 'weddingNights' => $this->weddingNights, 'currCode' => $curr->code, 'currSymbol' => $curr->symbol, 'price' => $price, 'adultprice' => $curr->convertPrice($adultPrice), 'childprice' => $curr->convertPrice($childPrice), 'infantprice' => $curr->convertPrice($infantPrice), 'subTotal' => $subTotal);
//end wedding details for booking page
        return $result;
    }

    function setDeposit($total)
    {
        if ($this->comm_type == "fixed") {
            $this->deposit = round($this->comm_value, 2);
        } else {
            $this->deposit = round(($total * $this->comm_value) / 100, 2);
        }
    }

    function setTax($amount)
    {
        if ($this->tax_type == "fixed") {
            $this->taxamount = round($this->tax_value, 2);
        } else {
            $this->taxamount = round(($amount * $this->tax_value) / 100, 2);
        }
    }

    function extrasFee($exts)
    {
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
    function getUpdatedDataBookResultObject($weddingid, $adults = 1, $child = 0, $infant = 0, $extras)
    {
        $this->ci->load->library('currconverter');
        $result = array();
        $curr = $this->ci->currconverter;
        $extratotal = $this->extrasFee($extras);
        $extTotal = $extratotal['extrasTotalFee'];
        $paymethodTotal = 0; //$this->paymethodFee($this->ci->input->post('paymethod'),$total);
        $this->set_id($weddingid);
        $this->wedding_short_details();
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
        } else {
            $childSubitem = "";
        }
        if ($infant > 0) {
            $infantSubitem = array("price" => $curr->convertPriceFloat($this->infantPrice), "count" => $infant);
        } else {
            $infantSubitem = "";
        }
        $subitem = array("adults" => $adultsSubitem, "child" => $childSubitem, "infant" => $infantSubitem);
        $result = (object)array('grandTotal' => $price, 'taxAmount' => $taxAmount, 'depositAmount' => $depositAmount, 'extrashtml' => $extrasHtml, 'bookingType' => "wedding", 'currCode' => $curr->code, 'stay' => 1, 'currSymbol' => $curr->symbol, 'subitem' => $subitem, 'extrasInfo' => $extratotal);
//end wedding details for booking page
        return json_encode($result);
    }

    public function checkErrors($maxAdults, $maxChild, $maxInfant)
    {
        if ($maxAdults < $this->adults) {
            $this->error = true;
            $this->errorCode = "0455";
        } elseif ($maxChild < $this->child) {
            $this->error = true;
            $this->errorCode = "0456";
        } elseif ($maxInfant < $this->infants) {
            $this->error = true;
            $this->errorCode = "0457";
        }
    }

//convert price
    public function convertAmount($price)
    {
        $this->ci->load->library('currconverter');
        $curr = $this->ci->currconverter;
        return $curr->convertPriceFloat($price);
    }

    public function convertPriceRange($price)
    {
        $this->ci->load->library('currconverter');
        $curr = $this->ci->currconverter;
        return $curr->convertPriceRange($price, 0);
    }

    public function priceRange($sprice)
    {
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

    public function weddingByLocations($totalnums = 7)
    {
        $locData = new stdClass;
        $this->db->select('location_id,wedding_id');
        $this->db->where('position', '1');
        $this->db->group_by('location_id');
        $result = $this->db->get('pt_wedding_locations')->result();
        foreach ($result as $rs) {
            $this->db->select('wedding_id');
            $this->db->where('wedding_location', $rs->location_id);
            $this->db->where('wedding_status', 'Yes');
            $wedding = $this->db->get('pt_wedding')->result();
            /*$weddingData = $this->getSingleResultObject($rs->wedding_id);*/
            $locationInfo = pt_LocationsInfo($rs->location_id, $this->lang);
            $locData->locations[] = (object)array('id' => $rs->location_id, 'name' => $locationInfo->city, 'count' => count($wedding), 'wedding' => $wedding);
        }
        usort($locData->locations, array($this, "cmp"));
        $locData->locations = array_slice($locData->locations, 0, $totalnums);
        return $locData;
    }

    function cmp($a, $b)
    {
        return $a->count < $b->count;
    }

    public function siteMapData()
    {
        $weddingData = array();
        $this->db->select('wedding_id');
        $this->db->where('wedding_status', 'Yes');
        $result = $this->db->get('pt_wedding');
        $wedding = $result->result();
        if (!empty($wedding)) {
            $weddingData = $this->getLimitedResultObject($wedding);
        }
        return $weddingData;
    }

    public function suggestionResults($query)
    {
        $response = array();
        $this->db->select('pt_wedding_translation.trans_title as title, pt_wedding.wedding_id as id,pt_wedding.wedding_title as title');
        $this->db->like('pt_wedding.wedding_title', $query);
        $this->db->or_like('pt_wedding_translation.trans_title', $query);
        $this->db->join('pt_wedding_translation', 'pt_wedding.wedding_id = pt_wedding_translation.item_id', 'left');
        $this->db->group_by('pt_wedding.wedding_id');
        $this->db->limit('25');
        $res = $this->db->get('pt_wedding')->result();
        $wedding = array();
        $locations = array();
        $this->db->select('pt_locations.id,pt_locations.location');
        $this->db->like('pt_locations.location', $query);
//$this->db->or_like('pt_locations.country',$query);
        $this->db->limit('25');
        $this->db->group_by('pt_locations.id');
        $this->db->join('pt_wedding_locations', 'pt_locations.id = pt_wedding_locations.location_id');
        $locres = $this->db->get('pt_locations')->result();
        if (!empty($locres)) {
            foreach ($locres as $l) {
                $lc++;
                $locInfo = pt_LocationsInfo($l->id, $this->lang);
                $locations[] = (object)array('id' => $l->id, 'text' => $locInfo->city . ", " . $locInfo->country, 'module' => 'location', 'disabled' => false);
            }
        }
        if (!empty($res)) {
            foreach ($res as $r) {
                $title = $this->get_title($r->title, $r->id);
                $wedding[] = (object)array('id' => $r->id, 'text' => trim($title), 'module' => 'wedding', 'disabled' => false);
            }
        }
        $tt = array("text" => "Wedding", "children" => $wedding);
        $ll = array("text" => "Locations", "children" => $locations);
        if (!empty($wedding)) {
            $response[] = $tt;
        }
        if (!empty($locations)) {
            $response[] = $ll;
        }

        $dataResponse = $response;
        return $dataResponse;
    }

    function getLatestWeddingForAPI()
    {
        $this->ci->db->select('wedding_id,wedding_created_at');
        $this->ci->db->order_by('wedding_created_at', 'desc');
        $this->ci->db->limit('10');
        $items = $this->ci->db->get('pt_wedding')->result();
        $this->ci->load->library('currconverter');
        $result = array();
        $curr = $this->ci->currconverter;
        if (!empty($items)) {
            foreach ($items as $h) {
                $this->set_id($h->wedding_id);
                $this->wedding_short_details();
                $adultprice = $this->adultPrice * $this->adults;
                $price = $curr->convertPrice($adultprice);
                $avgReviews = $this->weddingReviewsAvg();
                if (!empty($this->title)) {
                    $result[] = (object)array('id' => $h->wedding_id, 'title' => $this->title, 'thumbnail' => $this->thumbnail, 'starsCount' => $this->stars, 'location' => $this->location, 'price' => $price, 'currCode' => $curr->code, 'currSymbol' => $curr->symbol, 'avgReviews' => $avgReviews, 'createdAt' => $this->createdAt, 'module' => 'wedding');
                }
            }
        }
        return $result;
    }
}
