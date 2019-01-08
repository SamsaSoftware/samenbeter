<?php

namespace Api\Response;

class ApiResponse
{

    public $errNum;

    public $errFlag;

    public $errMsg;

    public static $messageArray = array (
        1 =>
            array (
                'sid' => '1',
                'statusNumber' => '1',
                'statusMessage' => 'Mandatory field missing.',
            ),
        2 =>
            array (
                'sid' => '2',
                'statusNumber' => '1',
                'statusMessage' => 'Email already registered, choose other.',
            ),
        3 =>
            array (
                'sid' => '3',
                'statusNumber' => '1',
                'statusMessage' => 'Error occurred while processing your request.',
            ),
        4 =>
            array (
                'sid' => '4',
                'statusNumber' => '1',
                'statusMessage' => 'Device type not supported.',
            ),
        5 =>
            array (
                'sid' => '5',
                'statusNumber' => '0',
                'statusMessage' => 'Signup completed!',
            ),
        6 =>
            array (
                'sid' => '6',
                'statusNumber' => '1',
                'statusMessage' => 'Session token expired, please login.',
            ),
        7 =>
            array (
                'sid' => '7',
                'statusNumber' => '1',
                'statusMessage' => 'Invalid token, please login or register.',
            ),
        8 =>
            array (
                'sid' => '8',
                'statusNumber' => '1',
                'statusMessage' => 'The email or password you entered is incorrect.',
            ),
        9 =>
            array (
                'sid' => '9',
                'statusNumber' => '0',
                'statusMessage' => 'Login completed!',
            ),
        10 =>
            array (
                'sid' => '10',
                'statusNumber' => '1',
                'statusMessage' => 'Your profile is under verification, One of our representatives will get in touch with you in the next 24 hours to setup your account',
            ),
        11 =>
            array (
                'sid' => '11',
                'statusNumber' => '1',
                'statusMessage' => 'Thank you for providing your details! We are not available in your area yet, but will inform you as soon as we are!',
            ),
        12 =>
            array (
                'sid' => '12',
                'statusNumber' => '0',
                'statusMessage' => 'Thank you for providing your details! One of our agents will contact you in the next 24 hours to complete your registration!',
            ),
        13 =>
            array (
                'sid' => '13',
                'statusNumber' => '1',
                'statusMessage' => 'Multiple logins not supported!',
            ),
        14 =>
            array (
                'sid' => '14',
                'statusNumber' => '1',
                'statusMessage' => 'Please accept terms and conditions.',
            ),
        15 =>
            array (
                'sid' => '15',
                'statusNumber' => '1',
                'statusMessage' => 'Please accept pricing conditions.',
            ),
        16 =>
            array (
                'sid' => '16',
                'statusNumber' => '1',
                'statusMessage' => 'Stripe error messages',
            ),
        17 =>
            array (
                'sid' => '17',
                'statusNumber' => '0',
                'statusMessage' => 'Upload Completed!',
            ),
        18 =>
            array (
                'sid' => '18',
                'statusNumber' => '1',
                'statusMessage' => 'Chunk size greater than 1 MB.',
            ),
        19 =>
            array (
                'sid' => '19',
                'statusNumber' => '1',
                'statusMessage' => 'Uploading failed.',
            ),
        20 =>
            array (
                'sid' => '20',
                'statusNumber' => '1',
                'statusMessage' => 'User not available.',
            ),
        21 =>
            array (
                'sid' => '21',
                'statusNumber' => '0',
                'statusMessage' => 'Got the details!',
            ),
        22 =>
            array (
                'sid' => '22',
                'statusNumber' => '1',
                'statusMessage' => 'Update failed.',
            ),
        23 =>
            array (
                'sid' => '23',
                'statusNumber' => '0',
                'statusMessage' => 'Updated!',
            ),
        24 =>
            array (
                'sid' => '24',
                'statusNumber' => '1',
                'statusMessage' => 'Seems email does not exist, please check once.',
            ),
        25 =>
            array (
                'sid' => '25',
                'statusNumber' => '1',
                'statusMessage' => 'Please check the email once, it seems wrong.',
            ),
        26 =>
            array (
                'sid' => '26',
                'statusNumber' => '1',
                'statusMessage' => 'Image type not supported.',
            ),
        27 =>
            array (
                'sid' => '27',
                'statusNumber' => '0',
                'statusMessage' => 'Got the reviews!',
            ),
        28 =>
            array (
                'sid' => '28',
                'statusNumber' => '1',
                'statusMessage' => 'Reviews not available.',
            ),
        29 =>
            array (
                'sid' => '29',
                'statusNumber' => '0',
                'statusMessage' => 'Logged out!',
            ),
        30 =>
            array (
                'sid' => '30',
                'statusNumber' => '1',
                'statusMessage' => 'No appointments on this date.',
            ),
        31 =>
            array (
                'sid' => '31',
                'statusNumber' => '0',
                'statusMessage' => 'Got Bookings!',
            ),
        32 =>
            array (
                'sid' => '32',
                'statusNumber' => '1',
                'statusMessage' => 'History not available.',
            ),
        33 =>
            array (
                'sid' => '33',
                'statusNumber' => '0',
                'statusMessage' => 'Got the details!',
            ),
        34 =>
            array (
                'sid' => '34',
                'statusNumber' => '0',
                'statusMessage' => 'Email is valid.',
            ),
        35 =>
            array (
                'sid' => '35',
                'statusNumber' => '0',
                'statusMessage' => 'Got the schedule!',
            ),
        36 =>
            array (
                'sid' => '36',
                'statusNumber' => '0',
                'statusMessage' => 'Schedule unavailable.',
            ),
        37 =>
            array (
                'sid' => '37',
                'statusNumber' => '1',
                'statusMessage' => 'Driver unavailable.',
            ),
        38 =>
            array (
                'sid' => '38',
                'statusNumber' => '1',
                'statusMessage' => 'Driver type unavailable.',
            ),
        39 =>
            array (
                'sid' => '39',
                'statusNumber' => '0',
                'statusMessage' => 'Request submitted, you will get a confirmation message when driver responds!',
            ),
        40 =>
            array (
                'sid' => '40',
                'statusNumber' => '0',
                'statusMessage' => 'Booking updated successfully!',
            ),
        41 =>
            array (
                'sid' => '41',
                'statusNumber' => '1',
                'statusMessage' => 'Sorry, passenger have cancelled this appointment.',
            ),
        42 =>
            array (
                'sid' => '42',
                'statusNumber' => '0',
                'statusMessage' => 'Booking cancelled.',
            ),
        43 =>
            array (
                'sid' => '43',
                'statusNumber' => '0',
                'statusMessage' => 'Booking cancelled, you will be charged for cancellation as the cancellation period is not with in the range.',
            ),
        44 =>
            array (
                'sid' => '44',
                'statusNumber' => '1',
                'statusMessage' => 'You can not cancel an rejected appointment.',
            ),
        45 =>
            array (
                'sid' => '45',
                'statusNumber' => '0',
                'statusMessage' => 'Zipcode available.',
            ),
        46 =>
            array (
                'sid' => '46',
                'statusNumber' => '1',
                'statusMessage' => 'We are not available in your area yet, Thank you.',
            ),
        47 =>
            array (
                'sid' => '47',
                'statusNumber' => '0',
                'statusMessage' => 'Email and zip code are available',
            ),
        48 =>
            array (
                'sid' => '48',
                'statusNumber' => '1',
                'statusMessage' => 'We are not available in your area yet, Thank you.',
            ),
        49 =>
            array (
                'sid' => '49',
                'statusNumber' => '1',
                'statusMessage' => 'Booking date or time not found.',
            ),
        50 =>
            array (
                'sid' => '50',
                'statusNumber' => '0',
                'statusMessage' => 'Card added!',
            ),
        51 =>
            array (
                'sid' => '51',
                'statusNumber' => '1',
                'statusMessage' => 'Cards not found.',
            ),
        52 =>
            array (
                'sid' => '52',
                'statusNumber' => '0',
                'statusMessage' => 'Cards found!',
            ),
        53 =>
            array (
                'sid' => '53',
                'statusNumber' => '1',
                'statusMessage' => 'This card is already added!',
            ),
        54 =>
            array (
                'sid' => '54',
                'statusNumber' => '0',
                'statusMessage' => 'Profile updated!',
            ),
        55 =>
            array (
                'sid' => '55',
                'statusNumber' => '0',
                'statusMessage' => 'Card removed!',
            ),
        56 =>
            array (
                'sid' => '56',
                'statusNumber' => '1',
                'statusMessage' => 'Invalid status, cannot update.',
            ),
        57 =>
            array (
                'sid' => '57',
                'statusNumber' => '0',
                'statusMessage' => 'Status updated as on the way.',
            ),
        58 =>
            array (
                'sid' => '58',
                'statusNumber' => '0',
                'statusMessage' => 'Status updated as arrived.',
            ),
        59 =>
            array (
                'sid' => '59',
                'statusNumber' => '0',
                'statusMessage' => 'Status updated as appointment completed.',
            ),
        60 =>
            array (
                'sid' => '60',
                'statusNumber' => '1',
                'statusMessage' => 'You have already accepted another appointment for the same date and time.',
            ),
        61 =>
            array (
                'sid' => '61',
                'statusNumber' => '0',
                'statusMessage' => 'Check your email. We\'ve sent you a link you can use to reset your password.',
            ),
        62 =>
            array (
                'sid' => '62',
                'statusNumber' => '1',
                'statusMessage' => 'Booking not found.',
            ),
        63 =>
            array (
                'sid' => '63',
                'statusNumber' => '0',
                'statusMessage' => 'Review updated!',
            ),
        64 =>
            array (
                'sid' => '64',
                'statusNumber' => '1',
                'statusMessage' => 'No one around you currently, please try after some time.',
            ),
        65 =>
            array (
                'sid' => '65',
                'statusNumber' => '1',
                'statusMessage' => 'No bookings for this month.',
            ),
        66 =>
            array (
                'sid' => '66',
                'statusNumber' => '1',
                'statusMessage' => 'Email is not registered.',
            ),
        67 =>
            array (
                'sid' => '67',
                'statusNumber' => '0',
                'statusMessage' => 'Reset password instructions are sent to your registered mail, please follow them.',
            ),
        68 =>
            array (
                'sid' => '68',
                'statusNumber' => '1',
                'statusMessage' => 'Unable to send email, please try after some time.',
            ),
        69 =>
            array (
                'sid' => '69',
                'statusNumber' => '0',
                'statusMessage' => 'Status updated.',
            ),
        70 =>
            array (
                'sid' => '70',
                'statusNumber' => '1',
                'statusMessage' => 'Update failed.',
            ),
        71 =>
            array (
                'sid' => '71',
                'statusNumber' => '1',
                'statusMessage' => 'Currently all our drivers are busy serving passengers, please try after some time.',
            ),
        72 =>
            array (
                'sid' => '72',
                'statusNumber' => '1',
                'statusMessage' => 'Sorry! this booking has now expired! Please be more active on the app to get good leads!',
            ),
        73 =>
            array (
                'sid' => '73',
                'statusNumber' => '0',
                'statusMessage' => 'Session available.',
            ),
        74 =>
            array (
                'sid' => '74',
                'statusNumber' => '0',
                'statusMessage' => 'Booking request cancelled!',
            ),
        75 =>
            array (
                'sid' => '75',
                'statusNumber' => '1',
                'statusMessage' => 'Booking already completed!',
            ),
        76 =>
            array (
                'sid' => '76',
                'statusNumber' => '1',
                'statusMessage' => 'The car id provided is not available currently.',
            ),
        77 =>
            array (
                'sid' => '77',
                'statusNumber' => '1',
                'statusMessage' => 'Check car id once, seems unavailable in your company.',
            ),
        78 =>
            array (
                'sid' => '78',
                'statusNumber' => '0',
                'statusMessage' => 'Thanks for the booking ! We will get the best possible cab and send you the booking details shortly !',
            ),
        79 =>
            array (
                'sid' => '79',
                'statusNumber' => '1',
                'statusMessage' => 'Your profile got rejected by our admin, please contact our support for further queries.',
            ),
        80 =>
            array (
                'sid' => '80',
                'statusNumber' => '1',
                'statusMessage' => 'Thanks for your effort, we are not available in your city yet.',
            ),
        81 =>
            array (
                'sid' => '81',
                'statusNumber' => '0',
                'statusMessage' => 'Yes, we are available in this area.',
            ),
        82 =>
            array (
                'sid' => '82',
                'statusNumber' => '1',
                'statusMessage' => 'Driver cancelled this booking.',
            ),
        83 =>
            array (
                'sid' => '83',
                'statusNumber' => '0',
                'statusMessage' => 'Updated as Journey started.',
            ),
        84 =>
            array (
                'sid' => '84',
                'statusNumber' => '0',
                'statusMessage' => 'Payment done for the journey.',
            ),
        85 =>
            array (
                'sid' => '85',
                'statusNumber' => '0',
                'statusMessage' => 'Dispute reported.',
            ),
        86 =>
            array (
                'sid' => '86',
                'statusNumber' => '1',
                'statusMessage' => 'Dispute reporting failed.',
            ),
        87 =>
            array (
                'sid' => '87',
                'statusNumber' => '1',
                'statusMessage' => 'Payment completed.',
            ),
        88 =>
            array (
                'sid' => '88',
                'statusNumber' => '0',
                'statusMessage' => 'Details updated!',
            ),
        89 =>
            array (
                'sid' => '89',
                'statusNumber' => '0',
                'statusMessage' => 'Session updated!',
            ),
        90 =>
            array (
                'sid' => '90',
                'statusNumber' => '1',
                'statusMessage' => 'Session not found.',
            ),
        91 =>
            array (
                'sid' => '91',
                'statusNumber' => '1',
                'statusMessage' => 'Please make sure job is started.',
            ),
        92 =>
            array (
                'sid' => '92',
                'statusNumber' => '1',
                'statusMessage' => 'Message can not be more than 200 characters.',
            ),
        93 =>
            array (
                'sid' => '93',
                'statusNumber' => '0',
                'statusMessage' => 'Message sent !',
            ),
        94 =>
            array (
                'sid' => '94',
                'statusNumber' => '0',
                'statusMessage' => 'Got favorites!',
            ),
        95 =>
            array (
                'sid' => '95',
                'statusNumber' => '1',
                'statusMessage' => 'No favorites found.',
            ),
        96 =>
            array (
                'sid' => '96',
                'statusNumber' => '0',
                'statusMessage' => 'Favorite removed.',
            ),
        97 =>
            array (
                'sid' => '97',
                'statusNumber' => '1',
                'statusMessage' => 'Your company got rejected or suspended by our admin, please contact your company for further details.',
            ),
        98 =>
            array (
                'sid' => '98',
                'statusNumber' => '1',
                'statusMessage' => 'The driver already accepted the booking.',
            ),
        99 =>
            array (
                'sid' => '99',
                'statusNumber' => '1',
                'statusMessage' => 'Your account is been deactivated by our admin, please write an email to info@OrderTapp.com to know more details.',
            ),
        100 =>
            array (
                'sid' => '100',
                'statusNumber' => '1',
                'statusMessage' => 'Final price is out of limit, please check once.',
            ),
        101 =>
            array (
                'sid' => '101',
                'statusNumber' => '1',
                'statusMessage' => 'You have been logged out of this device as you recently logged in from another device! If this was not you, please contact the OrderTapp customer care immediately!',
            ),
        102 =>
            array (
                'sid' => '102',
                'statusNumber' => '1',
                'statusMessage' => 'No appointment available to update tip currently',
            ),
        103 =>
            array (
                'sid' => '103',
                'statusNumber' => '0',
                'statusMessage' => 'Tip updated, thank you.',
            ),
        104 =>
            array (
                'sid' => '104',
                'statusNumber' => '0',
                'statusMessage' => 'Referral code updated successfully',
            ),
        105 =>
            array (
                'sid' => '105',
                'statusNumber' => '1',
                'statusMessage' => 'Coupon invalid or expired',
            ),
        106 =>
            array (
                'sid' => '106',
                'statusNumber' => '0',
                'statusMessage' => 'Valid coupon',
            ),
        107 =>
            array (
                'sid' => '107',
                'statusNumber' => '0',
                'statusMessage' => 'Paypal added successfully',
            ),
        108 =>
            array (
                'sid' => '108',
                'statusNumber' => '1',
                'statusMessage' => 'Failed to add paypal account',
            ),
        109 =>
            array (
                'sid' => '109',
                'statusNumber' => '1',
                'statusMessage' => 'Add card for payment',
            ),
        110 =>
            array (
                'sid' => '110',
                'statusNumber' => '0',
                'statusMessage' => 'Paypal removed',
            ),
        111 =>
            array (
                'sid' => '111',
                'statusNumber' => '1',
                'statusMessage' => 'Error while removing paypal, please try again',
            ),
        112 =>
            array (
                'sid' => '112',
                'statusNumber' => '1',
                'statusMessage' => 'Mobile number is already registered, choose other.',
            ),
        113 =>
            array (
                'sid' => '113',
                'statusNumber' => '0',
                'statusMessage' => 'Code sent.',
            ),
        114 =>
            array (
                'sid' => '114',
                'statusNumber' => '1',
                'statusMessage' => 'Unable to send code, please try again.',
            ),
        115 =>
            array (
                'sid' => '115',
                'statusNumber' => '0',
                'statusMessage' => 'Phone verified.',
            ),
        116 =>
            array (
                'sid' => '116',
                'statusNumber' => '1',
                'statusMessage' => 'Verification failed, try again.',
            ),
        117 =>
            array (
                'sid' => '117',
                'statusNumber' => '1',
                'statusMessage' => 'Please Signup First',
            ),
        118 =>
            array (
                'sid' => '118',
                'statusNumber' => '0',
                'statusMessage' => 'Mobile number available',
            ),
        119 =>
            array (
                'sid' => '119',
                'statusNumber' => '1',
                'statusMessage' => 'Business info is not available',
            ),
        120 =>
            array (
                'sid' => '120',
                'statusNumber' => '0',
                'statusMessage' => 'Order updated',
            ),
        121 =>
            array (
                'sid' => '121',
                'statusNumber' => '0',
                'statusMessage' => 'Status updated',
            ),
        122 =>
            array (
                'sid' => '122',
                'statusNumber' => '0',
                'statusMessage' => 'Congratulations, you got %s discount on your order.',
            ),
        123 =>
            array (
                'sid' => '123',
                'statusNumber' => '1',
                'statusMessage' => 'Sorry, the minimum order must be atleast %s.',
            ),
        124 =>
            array (
                'sid' => '124',
                'statusNumber' => '1',
                'statusMessage' => 'Failed to authorise the transaction.',
            ),
        125 =>
            array (
                'sid' => '125',
                'statusNumber' => '1',
                'statusMessage' => 'Please check your password once.',
            ),
        126 =>
            array (
                'sid' => '126',
                'statusNumber' => '1',
                'statusMessage' => 'You have to make a Post Request',
            ),
    );

    public function __construct($errorNumber, $text = '', $data = array())
    {
        $msg = self::$messageArray[$errorNumber];


        $this->errNum = $msg['sid'];
        $this->errFlag = $msg['statusNumber'];
        $this->errMsg = $msg['statusMessage'];
        if ($errorNumber == '1') {
            $this->errMsg = $text . " is missing.";
        }
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getErrNum()
    {
        return $this->errNum;
    }

    /**
     * @param mixed $errNum
     */
    public function setErrNum($errNum)
    {
        $this->errNum = $errNum;
    }

    /**
     * @return mixed
     */
    public function getErrFlag()
    {
        return $this->errFlag;
    }

    /**
     * @param mixed $errFlag
     */
    public function setErrFlag($errFlag)
    {
        $this->errFlag = $errFlag;
    }

    /**
     * @return mixed
     */
    public function getErrMsg()
    {
        return $this->errMsg;
    }

    /**
     * @param mixed $errMsg
     */
    public function setErrMsg($errMsg)
    {
        $this->errMsg = $errMsg;
    }


    public function getStatusMessage($errorNumber, $text)
    {

    }

}