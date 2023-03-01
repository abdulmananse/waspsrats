<?php

use App\Models\Company;
use App\Models\Country;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\District;
use App\Models\SMSLog;
use App\Models\Logs;
use App\Models\Service;

/**
* Encode Id
*
* @param $id
* @return mix
*/
if (!function_exists('encodeId')) {

    function encodeId($id) {
        return Hashids::encode($id);
    }

}

/**
* Image Decode
*
* @param $fileName
* @return mix
*/
if (!function_exists('imageNameDecode')) {

    function imageNameDecode($fileName) {

        $firstStr = str_replace("twiiiyui=", "==", $fileName);
        $secondStr = base64_decode($firstStr);
        return str_replace('rootidr', "", $secondStr);
    }

}

/**
* Decode Id
*
* @param $id
* @return mix
*/
if (!function_exists('decodeId')) {

    function decodeId($id) {
        $ids = Hashids::decode($id);
        if (count($ids) > 0) {
            return $ids[0];
        }

        return 0;
    }

}

/**
* Get NFS Path
*
* @param $directory
* @param $public_path
* @return mix
*/
if (!function_exists('get_nfs_path')) {

    function get_nfs_path($directory = '', $public_path = false) {
        $directory = 'uploads/cepris-uploads/' . $directory;
        $path = asset($directory . '/');
        if ($public_path) {
            $path = public_path($directory . '/');
        }

//        if (in_array(config('app.env'), array('staging'))) {
//            $path = '/NFS-DATA/' . $directory . '/';
//        }
        return $path;
    }

}

/**
* Upload Image
*
* @param $image
* @param $dir
* @return mix
*/
if (!function_exists('uploadImage')) {

    function uploadImage($image, $dir = '') {
        try {
            $name = time() . '.' . $image->getClientOriginalExtension();
            $newName = md5(rand(0, 100000) . strtotime(date('Y-m-d H:i:s') . microtime())) . $name;
            $destinationPath = get_nfs_path($dir, true);
            $image->move($destinationPath, $newName);
            return $newName;
        } catch (Exception $e) {
            Log::error('Image Upload Error: ' . $e->getMessage());
            return '';
        }
    }

}

/**
* Read file data
*
* @param $directory
* @param $fileName
* @return mix
*/
if (!function_exists('read_file')) {

    function read_file($directory = false, $fileName = false, $link = true) {
        if (!$fileName || !$directory) {
            return false;
        }
        
//        if (in_array(config('app.env'), array('staging'))) {
//            $imgString = str_replace("==", "twiiiyui=", base64_encode(get_nfs_path($directory) . $fileName));
//            if (strpos($fileName, '.pdf')) {
//                return route('display-pdf', $imgString);
//            } else {
//                return route('display-image', $imgString);
//            }
//        } else {
            if (strpos($fileName, '.pdf') && !$link) {
                return asset('images/1200px-PDF_file_icon.png');
            } else {
                return URL::to(get_nfs_path($directory) . '/' . $fileName);
            }
        //}
    }

}

/**
* Get Admin Data
*
* @return mix
*/
if (!function_exists('getAdmin')) {

    function getAdmin() {
        return User::where('email', config('app.admin_email'))->first();
    }

}

/**
* Check Active Route
*
* @param $route
* @param $output
* @return mix
*/
if (!function_exists('isActiveRoute')) {

    function isActiveRoute($route, $output = "active") {
        if (Route::current()->uri == $route)
            return $output;
    }

}

/**
* Active Route
*
* @param $paths
* @param $class
* @return mix
*/
if (!function_exists('setActive')) {

    function setActive($paths, $class = TRUE, $className = 'menu-item-active') {
        foreach ($paths as $path) {

            if (Request::is($path . '*')) {
                if ($class)
                    return ' class=menu-item-active';
                else
                    return ' ' . $className;
            }
        }
    }

}

/**
* Active Routes
*
* @param $routes
* @param $output
* @return mix
*/
if (!function_exists('areActiveRoutes')) {

    function areActiveRoutes(Array $routes, $output = "active") {
        foreach ($routes as $route) {
            if (Route::current()->uri == $route) {
                return $output;
            }
        }
    }

}

/**
* Send Message to Mobile Number
*
* @param $number
* @param $message
* @return mix
*/
if (!function_exists('sendOtpMessage')) {

    function sendOtpMessage($number, $otp, $cnic = '')
    {
        $message = __('messages.your_otp_is', ['otp' => $otp]);
        sendSms($number, $message, $cnic, 'otp');
    }

}

/**
* Send Message to Mobile Number
*
* @param $number
* @param $message
* @return mix
*/
if (!function_exists('sendPasswordMessage')) {

    function sendPasswordMessage($number, $password, $cnic = '')
    {
        $message = __('messages.your_password_is', ['password' => $password]);
        sendSms($number, $message, $cnic, 'password');
    }

}

/**
* Send Message to Mobile Number
*
* @param $number
* @param $message
* @return mix
*/
if (!function_exists('sendSms')) {

    function sendSms($number, $message, $cnic = null, $type = null, $logId = 0)
    {
        
        $twoDigit = substr($number, 0, 2);
        if ($twoDigit != 92) {
            $number = '92'. substr($number, 1);
        }
        
        $request = [
            'ContactNumber' => $number,
            'SmsContent' => $message,
            'LogId' => $logId,
            'SecurityKey' => config('app.sms_key')
        ];
        
        $smsLog = SMSLog::create([
            'cnic' => $cnic,
            'mobile' => $number,
            'request' => json_encode($request),
            'request_time' => date('Y-m-d H:i:s'),
            'type' => $type
        ]);
        
        if ($logId > 0) {
            // log step 12
            saveLogs([
                'log_id' => $logId,
                'sms_status' => 0,
                'sms_request' => json_encode($request),
                'sms_request_time' => date('Y-m-d H:i:s'),
                'status' => 12
            ]);
        }

        Log::channel('sms')->info('PSPA SMS Request Data: ' . json_encode($request));

        try {
            $response = Http::post(config('app.sms_url'), $request);
            
            $resData = $response->object();

            $smsLog->response = $response->json();
            $smsLog->response_time = date('Y-m-d H:i:s');
            
             
            if (isset($resData) && $resData->Message == "Success") {
                $smsStatus = 1;
            } else {
                $smsStatus= 2;
            }

            $smsLog->status = $smsStatus;
            $smsLog->save();

            if ($logId > 0) {
                // log step 13
                saveLogs([
                    'log_id' => $logId,
                    'sms_status' => $smsStatus,
                    'sms_response' => $response->json(),
                    'sms_response_time' => date('Y-m-d H:i:s'),
                    'status' => 13
                ]);
            }

            Log::channel('sms')->info('PSPA SMS Response: ', $response->json());
            
            return true;
        
         } catch (Exception $e) {
            Log::channel('sms')->error('SMS Error: ' . $e->getMessage());

            $smsRes = json_encode([
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);

            $smsLog->response = $smsRes;
            $smsLog->response_time = date('Y-m-d H:i:s');
            $smsLog->status = 2;
            $smsLog->save();

            if ($logId > 0) {
                // log step 14
                saveLogs([
                    'log_id' => $logId,
                    'sms_status' => 2,
                    'sms_response' => $smsRes,
                    'sms_response_time' => date('Y-m-d H:i:s'),
                    'status' => 14
                ]);
            }

            return false;
         }
    }

}

/**
* Save log data in database
*
* @param Array $logData 
* @return mix
*/
if (!function_exists('saveLogs')) {

    function saveLogs($logData = [])
    {

        if (@$logData['log_id'] > 0) {
            
            //Logs::where('id', $logData['log_id'])->update($logData);

            $log = Logs::find($logData['log_id']);
            if ($log) {
                $log->update($logData);
                //$log = $log->fresh();
                return $log;
            }
        } 
        else if (isset($logData['cnic']) && isset($logData['mobile'])) {
            $cnic = $logData['cnic'];
            $number = $logData['mobile'];

            if (isset($logData['request_from_pspa'])) {
                $log = Logs::create([
                    'cnic' => $cnic,
                    'mobile' => $number,
                    'request_from_pspa' => $logData['request_from_pspa'],
                    'request_from_pspa_time' => date('Y-m-d H:i:s')
                ]);
                
                //$log = $log->fresh();
                return $log;
            }
        }

        return false;
    }

}

/**
* Hide Digits
*
* @param $number
* @param $hideStart
* @param $hideEnd
* @param $showCharacter
* @return mix
*/
if (!function_exists('hideDigits')) {

    function hideDigits($number, $hideStart = 0, $hideEnd = 0, $showCharacter = '*')
    {
        return substr($number, 0, $hideStart) . str_pad('', (strlen($number) - $hideStart - $hideEnd), $showCharacter) . substr($number, - $hideEnd);
    }

}

/**
* Get Role Name
*
* @return mix
*/
if (!function_exists('roleName')) {

    function roleName()
    {
        $roleName = '';
        $user = Auth::user();
        if (@$user->getRoleNames()->first()) {
            $roleName = $user->getRoleNames()->first();
        }
            
        return $roleName;
    }

}

/**
* Get Role Name
*
* @return mix
*/
if (!function_exists('isSuperAdmin')) {

    function isSuperAdmin()
    {
        $isSuperAdmin = false;
        $user = Auth::user();
        if (@$user->getRoleNames()->first() && $user->getRoleNames()->first() == 'Super Admin') {
            $isSuperAdmin = true;
        }
            
        return $isSuperAdmin;
    }

}

/**
* Get Application Status Badge
*
* @param $status
* @return mix
*/
if (!function_exists('getStatusBadge')) {

    function getStatusBadge($status = 1)
    {
        $badge = '<span style="overflow: visible; position: relative; width: 130px;">';
                    
        switch ($status) {
            case 1:
                $badge .= '<a href="#" class="badge bg-success" > Active </a>';
                break;
            default:
                $badge .= '<a href="#" class="badge bg-danger" > In Active </a>';
        }
        
        $badge .= '</span>';    
        return $badge;
    }

}

/**
* Get Excepts Array
* @param $key
* @return mix
*/
if (!function_exists('getExceptsArray')) {

    function getExceptsArray($key = 0, $secKey = '')
    {
        $excepts['except_1'] = [1 => 'the 1st', 2 => 'the 2nd', 3 => 'the 3rd', 4 => 'the 4th'];
        $excepts['except_2'] = [1 => 'Mon of', 2 => 'Tue of', 3 => 'Wed of', 4 => 'Thu of', 5 => 'Fri of', 6 => 'Sat of', 7 => 'Sun of'];
        $excepts['except_3'] = [1 => 'every week', 2 => 'every month'];

        if ($key > 0 && $key <= 3) {
            if (!empty($secKey) && $secKey > 0) {
                return $excepts['except_' . $key][$secKey];
            } else {
                return '';
            }
            return isset($excepts['except_' . $key]) ? $excepts['except_' . $key] : '';
        }

        return $excepts;        
    }

}

/**
* Get Uuid
*
* @return mix
*/
if (!function_exists('getUuid')) {

    function getUuid()
    {
        //(string) \Uuid::generate(4);
        return \DB::raw('uuid()');
    }

}

/**
    * Append an ordinal indicator to a numeric value.
    *
    * @param  string|int  $value
    * @param  bool  $superscript
    * @return string
    */
if (! function_exists('str_ordinal')) {
    
    function str_ordinal($value, $superscript = false)
    {
        $number = abs($value);
 
        $indicators = ['th','st','nd','rd','th','th','th','th','th','th'];
 
        $suffix = $superscript ? '<sup>' . $indicators[$number % 10] . '</sup>' : $indicators[$number % 10];
        if ($number % 100 >= 11 && $number % 100 <= 13) {
            $suffix = $superscript ? '<sup>th</sup>' : 'th';
        }
 
        return number_format($number) . $suffix;
    }
}

/**
* Get District List
*
* @param $status
* @return mix
*/
if (!function_exists('getDistrictList')) {

    function getDistrictList($prepend = [])
    {
        $districts = District::pluck('name', 'id'); 
        return (empty($prepend))?$districts:$districts->prepend($prepend, '');
    }
}

/**
* Get Company List
*
* @param $prepend
* @return mix
*/
if (!function_exists('getCompanyList')) {

    function getCompanyList($prepend = [])
    {
        $companies = Company::pluck('name', 'id'); 
        return empty($prepend) ? $companies : $companies->prepend($prepend, '');
    }
}

/**
* Get Customer List
*
* @param $prepend
* @return mix
*/
if (!function_exists('getCustomerList')) {

    function getCustomerList($prepend = [])
    {
        $customers = [];
        //$customers = Customer::pluck('name', 'id'); 
        return empty($prepend) ? $customers : $customers->prepend($prepend, '');
    }
}

/**
* Get Service List
*
* @param $prepend
* @return mix
*/
if (!function_exists('getServiceList')) {

    function getServiceList($prepend = [])
    {
        $services = Service::pluck('name', 'id'); 
        return empty($prepend) ? $services : $services->prepend($prepend, '');
    }
}

/**
* Get Country List
*
* @param $prepend
* @return mix
*/
if (!function_exists('getCountryList')) {

    function getCountryList($prepend = [])
    {
        $countries = Country::pluck('name', 'id'); 
        return empty($prepend) ? $countries : $countries->prepend($prepend, '');
    }
}

/**
* dd with json
*
* @return mix
*/
if (!function_exists('dj')) {

    function dj($data)
    {
        return response()->json([
            'data' => $data
        ], 200);
        exit;
    }

}

/**
 * Success Response
 *
 * @param $message
 * @return JSON
 */
if (!function_exists('successResponse')) {
    function successResponse($message, $response = []) {
        $responseData = [
            'message' => $message,
            'code' => 200,
            'status' => true,
        ];

        $response = array_merge($responseData, $response);

        return response()->json($response, 200);
    }
}

/**
 * Error Response
 *
 * @param $message
 * @return JSON
 */
if (!function_exists('errorResponse')) {
    function errorResponse($message, $response = [], $status = 400) {
        $responseData = [
            'message' => $message,
            'code' => $status,
            'status' => false,
        ];

        $response = array_merge($responseData, $response);

        return response()->json($response, 200);
    }
    
}

function load_pdf($pdf) {
    header('Content-Type: application/pdf');
    $image_name = image_name_decode($pdf);
    readfile($image_name);
}

/**
* add Dashes In CNIC
*
* @return mix
*/
if (!function_exists('addDashesInCNIC')) {

    function addDashesInCNIC($cnic)
    {
        return preg_replace("/^(\d{5})(\d{7})(\d{1})$/", "$1-$2-$3", $cnic);
    }

}

/**
* add Dash In Mobile
*
* @return mix
*/
if (!function_exists('addDashInMobile')) {

    function addDashInMobile($mobile)
    {
        return preg_replace("/^(\d{4})(\d{7})$/", "$1-$2", $mobile);
    }

}

/**
 * Error Response
 *
 * @param $message
 * @return JSON
 */
if (!function_exists('get_zcity_code_by_city_name')) {
    function get_zcity_code_by_city_name($city_name) {
        $citiesList = [
            '10' => 'ABBOTABAD',
            '20' => 'ABDUL HAKEEM',
            '30' => 'AHMEDPUR EAST',
            '40' => 'AKORA KHATAK',
            '50' => 'ALIPUR CHATTA',
            '60' => 'ARIFWALA',
            '70' => 'ATTOCK',
            '80' => 'AWARAN',
            '90' => 'BADIN',
            '100' => 'BAGH',
            '120' => 'BAHAWALNAGAR',
            '130' => 'BAHAWALPUR',
            '140' => 'BAIPHERU',
            '150' => 'BAJUR',
            '160' => 'BALAKOT',
            '170' => 'BALTISTAN',
            '180' => 'BANGWAL',
            '190' => 'BANNU',
            '210' => 'BARKHAN',
            '220' => 'BATGRAM',
            '230' => 'BATKHELA',
            '240' => 'BELA',
            '250' => 'BHAKKAR',
            '260' => 'BHERA',
            '270' => 'BHIMBER',
            '280' => 'BHIRYA ROAD',
            '290' => 'BHITSHAH',
            '300' => 'BHUREWALA',
            '310' => 'BISHAM',
            '320' => 'BOLAN',
            '330' => 'BUNNER',
            '340' => 'CANTT SHORKOT',
            '350' => 'CHAGHI',
            '360' => 'CHAKLALA',
            '370' => 'CHAKWAL',
            '380' => 'CHAMAN',
            '390' => 'CHARSADDA',
            '400' => 'CHICHAWATNI',
            '410' => 'CHICHIKOMALIA',
            '420' => 'CHIKAR',
            '430' => 'CHINOT',
            '440' => 'CHISHTIAN',
            '450' => 'CHITRAL',
            '460' => 'CHOUK AZAM',
            '470' => 'CHOWKMUNDA',
            '480' => 'CHUNAIN',
            '490' => 'D G KHAN',
            '500' => 'D I KHAN',
            '510' => 'DADU',
            '520' => 'DAGAR',
            '530' => 'DALBADIN',
            '540' => 'DARA ADAMKHEL',
            '550' => 'DARIAKHAN',
            '560' => 'DASKA',
            '570' => 'DEENA',
            '580' => 'DEEPALPUR',
            '590' => 'DERA BUGHTI',
            '600' => 'DERA MURAD JAMALI',
            '610' => 'DHARKI',
            '620' => 'DHUDYAL',
            '630' => 'DIAMIR',
            '640' => 'DIR',
            '660' => 'DIRKOT',
            '670' => 'DUNYAPUR',
            '680' => 'EIMANABAD',
            '690' => 'ESSAKHEL',
            '700' => 'FAISALABAD',
            '710' => 'FAROOQAABAD',
            '720' => 'FATEH JANG',
            '730' => 'FATEHPUR',
            '740' => 'FORT ABBAS',
            '750' => 'GADOOON AMZAI',
            '760' => 'GAMBAT',
            '770' => 'GARMARAJA',
            '780' => 'GAWADAR',
            '790' => 'GHANCHEE',
            '800' => 'GHIZER',
            '810' => 'GHOTKI',
            '830' => 'GILGIT',
            '840' => 'GJHAKAR',
            '850' => 'GOJRA',
            '860' => 'GOMAL',
            '870' => 'GUJARKHAN',
            '880' => 'GUJRANWALA',
            '890' => 'GUJRAT',
            '900' => 'HAFIZABAD',
            '910' => 'HALA',
            '920' => 'HANGU', 
            '930' => 'HARIPUR',
            '940' => 'HAROONABAD',
            '950' => 'HARRAPA',
            '960' => 'HASILPUR',
            '970' => 'HASSAN ABDAL',
            '980' => 'HATIAN',
            '990' => 'HATTAR',
            '1000' => 'HAVEILI LAKKA',
            '1010' => 'HAVELIAN',
            '1020' => 'HAZARU',
            '1030' => 'HUB',
            '1040' => 'HUMAK',
            '1050' => 'HYDERABAD',
            '1060' => 'ISLAMABAD',
            '1070' => 'JACOBABAD',
            '1080' => 'JAFFARABAD',
            '1090' => 'JAHANIAN',
            '1100' => 'JALALPUR JATTA',
            '1102' => 'Jalal Pur Pirwala',
            '1104' => 'JAMSHORO',
            '1110' => 'JAMROOD',
            '1120' => 'JARANWALA',
            '1130' => 'JEHANGIRA',
            '1140' => 'JHAL MAGSI',
            '1150' => 'JHANG',
            '1160' => 'JHELUM',
            '1170' => 'JHUDO',
            '1180' => 'JOHARABAD',
            '1190' => 'KABIRWALA',
            '1200' => 'KAHAN GHARH',
            '1210' => 'KAHIRPUR TAMEWALI',
            '1220' => 'KAHKA DOGRA',
            '1230' => 'KAHUTA',
            '1240' => 'KAKUL',
            '1250' => 'KALABAGH',
            '1260' => 'KALORKOT',
            '1270' => 'KAMALIA',
            '1280' => 'KAMBER ALI KHAN',
            '1290' => 'KAMOKI',
            '1300' => 'KAMRA',
            '1310' => 'KANA KACHA',
            '1320' => 'KANDHKOT',
            '1330' => 'KANDIARO',
            '1340' => 'KANPUR',
            '1350' => 'KARACHI MALIR',
            '1360' => 'KARACHI CENTRAL',
            '1370' => 'KARACHI EAST',
            '1380' => 'KARACHI SOUTH',
            '1390' => 'KARACHI WEST',
            '1400' => 'KARAK',
            '1410' => 'KAROOR PAKKA',
            '1420' => 'KASHMORE',
            '1430' => 'KASUR',
            '1440' => 'KECH',
            '1450' => 'KHAIRPUR MIRIS',
            '1460' => 'KHANEWAL',
            '1470' => 'KHARAN',
            '1480' => 'KHEAIRAN',
            '1490' => 'KHIPRO',
            '1500' => 'KHUSHAB',
            '1510' => 'KHUZDAR',
            '1520' => 'KHYBER AGENCY',
            '1530' => 'KILA SAIFULLAH',
            '1540' => 'KILLA ABDULLAH',
            '1550' => 'KOHAT',
            '1560' => 'KOHISTAN',
            '1570' => 'KOHLU',
            '1580' => 'KOT MOMEN',
            '1590' => 'KOT NAJIBULLAH',
            '1600' => 'KOT RADHA KISHAN',
            '1610' => 'KOTADU',
            '1620' => 'KOTLI',
            '1622' => 'KOTRI',
            '1630' => 'KUNRI',
            '1640' => 'KURRAM AGENCY',
            '1650' => 'LAHORE',
            '1660' => 'LAKI MARWAT',
            '1680' => 'LALAMUSA',
            '1690' => 'LANDI KOTAL',
            '1700' => 'LAWRENCEPUR',
            '1710' => 'LARKANA',
            '1720' => 'LASBELA',
            '1730' => 'LATAMBER',
            '1740' => 'LAYYAH',
            '1750' => 'LIQUATABAD',
            '1760' => 'LODHRAN',
            '1770' => 'LORALAI',
            '1780' => 'MAILSI',
            '1790' => 'MALAKAND',
            '1800' => 'MAMUN KANJAN',
            '1810' => 'MANDI BAHAUDDIN',
            '1820' => 'MANGLA',
            '1830' => 'MANSEHRA',
            '1840' => 'MARDAN',
            '1850' => 'MASTUNG',
            '1860' => 'MATLI',
            '1870' => 'MATRI',
            '1880' => 'MEHRABPUR',
            '1890' => 'MIANWALI',
            '1900' => 'MINAN CHANU',
            '1910' => 'MINGORA',
            '1920' => 'MIR PUR KHAS',
            '1930' => 'MIRALI BNU',
            '1940' => 'MIRANSHAH',
            '1950' => 'MIRPUR AK',
            '1960' => 'MIRPUR MATHELO',
            '1970' => 'MITHAN KOT',
            '1980' => 'MOHMAND AGENCY',
            '1990' => 'MOR KUNDA',
            '2000' => 'MORO',
            '2010' => 'MULTAN',
            '2020' => 'MURRI',
            '2030' => 'MUSAKHEL',
            '2040' => 'MUSTAFAABAD',
            '2050' => 'MUZAFFARABAD',
            '2060' => 'MUZAFFARGARAH',
            '2070' => 'NAKYAL',
            '2080' => 'NANKANA SAHB',
            '2090' => 'NAROWAL',
            '2100' => 'NASIRABAD',
            '2110' => 'NATHIA GALI',
            '2120' => 'NAWABSHAH',
            '2130' => 'NAWANKALI',
            '2140' => 'NEW JATOI',
            '2160' => 'NOOROABAD',
            '2170' => 'NORANGMANDI',
            '2180' => 'NORTH WAZIRISTAN AGY',
            '2190' => 'NOSHKI',
            '2200' => 'NOWSHERA',
            '2210' => 'NOWSHERO FEROZ',
            '2220' => 'OKARA',
            '2230' => 'ORAKAZI AGENCY',
            '2240' => 'PADIDAN',
            '2250' => 'PAK PATAN',
            '2260' => 'PANJGUR',
            '2270' => 'PANO AQIL',
            '2280' => 'PARACHANAR',
            '2290' => 'PASHIN',
            '2300' => 'PASNI',
            '2310' => 'PASROOR',
            '2320' => 'PATOKI',
            '2330' => 'PESHAWAR',
            '2340' => 'PHALIA',
            '2350' => 'PINDDADAN KHAN',
            '2360' => 'PINDIBHATIAN',
            '2370' => 'PINDIGHEB',
            '2380' => 'PIR JO GOTH',
            '2390' => 'PIR MAHAL',
            '2400' => 'PISJHIN',
            '2410' => 'PLUNDRI',
            '2420' => 'POONCH',
            '2440' => 'QAIDABAD',
            '2450' => 'QALAT',
            '2460' => 'QAZI AHMED',
            '2470' => 'QILA DEEDAR SINGH',
            '2480' => 'QILLA SHEKHUPURA',
            '2490' => 'QUETTA',
            '2500' => 'RABWAH',
            '2520' => 'RAHIM YAR KHAN',
            '2530' => 'RAHWALI',
            '2540' => 'RAIWIND',
            '2550' => 'RAJANPUR',
            '2560' => 'RANI PUR',
            '2570' => 'RATODERO',
            '2580' => 'RAWAL KOT',
            '2590' => 'RAWALPINDI',
            '2600' => 'RAWAT',
            '2610' => 'RENLA KURD',
            '2620' => 'RISALPUR',
            '2630' => 'SADIQABAD',
            '2640' => 'SAHIWAL',
            '2650' => 'SAJAWAL',
            '2660' => 'SAKHA KOT',
            '2670' => 'SAMBRIAL',
            '2680' => 'SANGHAR',
            '2690' => 'SARAI MORANG',
            '2700' => 'SARGODHA',
            '2710' => 'SAWABI',
            '2720' => 'SEWAN SHARIF',
            '2730' => 'SHAH KOT',
            '2740' => 'SHAH PUR',
            '2750' => 'SHAHDADKOT',
            '2760' => 'SHAHDADPUR',
            '2770' => 'SHAKARGARAH',
            '2780' => 'SHANGLA HILL',
            '2790' => 'SHARKPUR',
            '2800' => 'SHEIKHUPURA',
            '2810' => 'SHIJAABAD',
            '2820' => 'SHIKARPUR',
            '2830' => 'SHORKOT',
            '2840' => 'SIALKOT',
            '2850' => 'SIBI',
            '2860' => 'SIDU SHARIF',
            '2870' => 'SIHALA',
            '2880' => 'SKARDU',
            '2890' => 'SKRAND',
            '2900' => 'SMANDRI',
            '2910' => 'SOUTH WAZIRISTAN AGY',
            '2920' => 'SUDHNOTI',
            '2930' => 'SUKKUR',
            '2940' => 'SWAT',
            '2950' => 'TAFTAN',
            '2960' => 'TAKHT BHAI',
            '2970' => 'TAKHT NUSRATI',
            '2980' => 'TALAGANG',
            '2990' => 'TALL',
            '3000' => 'TANDINAWALA',
            '3010' => 'TANDO ADAM KHAN',
            '3020' => 'TANDO ALLAHYAR',
            '3030' => 'TANDO BAGO',
            '3040' => 'TANDO JAM',
            '3050' => 'TANDO M. KHAN',
            '3060' => 'TANK',
            '3070' => 'TARU SHAH',
            '3080' => 'TAXILLAN',
            '3090' => 'THARPARKAR',
            '3100' => 'THATTA',
            '3110' => 'THULL',
            '3120' => 'TIMARGARAH',
            '3130' => 'TOBA TEK SINGH',
            '3140' => 'TUNSA',
            '3150' => 'TURBAT',
            '3160' => 'UCH NSHARIF',
            '3170' => 'UMER KOT',
            '3180' => 'USTA MUHAMMAD',
            '3190' => 'UTTAL',
            '3200' => 'VEHARI',
            '3210' => 'WAH CANNTT',
            '3220' => 'WAR',
            '3230' => 'WAZIRABAD',
            '3240' => 'WIRKAN',
            '3250' => 'YASMAN',
            '3260' => 'ZAFARWAL',
            '3270' => 'ZHOB',
            '3280' => 'ZIARAT',
            '3290' => 'Washuk',
            '3310' => 'NEELAM',
            '3320' => 'JUDBA'

        ];

        
    }
    
}