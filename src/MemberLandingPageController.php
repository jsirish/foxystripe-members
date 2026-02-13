<?php

namespace Dynamic\FoxyStripeMembers;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Security\Security;

class MemberLandingPageController extends \PageController
{
    /**
     * @var array
     */
    private static $allowed_actions = [
        'index',
    ];

    /**
     * @return bool|\SilverStripe\Control\HTTPResponse
     */
    public function checkMember()
    {
        if (Security::getCurrentUser()) {
            return true;
        } elseif ($MemberPage = FoxyCartMemberProfilePage::get()->First()) {
            return $this->redirect($MemberPage->Link());
        } else {
            return Security::permissionFailure($this, _t(
                'AccountPage.CANNOTCONFIRMLOGGEDIN',
                'Please login to view this page.'
            ));
        }
    }

    /**
     * @param HTTPRequest $request
     * @return array|HTTPResponse
     */
    public function index(HTTPRequest $request)
    {
        $response = $this->checkMember();
        if ($response instanceof HTTPResponse) {
            return $response;
        }
        return [];
    }
}
