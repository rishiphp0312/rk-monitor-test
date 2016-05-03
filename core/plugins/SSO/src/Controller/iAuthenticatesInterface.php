<?php

namespace SSO\Controller;

/*
 * iAuthenticates interface
 */

interface iAuthenticatesInterface {

    public function login($params = []);

    public function refreshTokenIdentifier($params = []);

    public function fetchsession($params = []);

    public function logout();
}
