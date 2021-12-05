<?php
namespace Destiny\Api;

use Destiny\Common\Application;
use Destiny\Common\Utils\Date;
use Destiny\Common\Service;

class ApiAuthenticationService extends Service {
    
    /**
     * Singleton
     *
     * @var ApiAuthenticationService
     */
    protected static $instance = null;
    
    /**
     * Salting for the auth token
     *
     * @var string
     */
    protected $authTokenSalt = '_r52Ax_';

    /**
     * Singleton instance
     *
     * @return ApiAuthenticationService
     */
    public static function instance() {
        return parent::instance ();
    }

    /**
     * Builds a unique auth token
     *
     * @return string
     */
    public function createAuthToken($userId) {
        $token = md5 ( $this->authTokenSalt . microtime ( true ) . $userId );
        if ($this->getAuthTokenExists ( $token )) {
            $token = $this->createAuthToken ( $userId );
        }
        return $token;
    }

    /**
     * Add a API auth token to a user
     *
     * @param int $userId
     * @param string $authToken
     */
    public function addAuthToken($userId, $authToken) {
        $conn = Application::instance ()->getConnection ();
        $conn->insert ( 'dfl_users_auth_token', array (
            'userId' => $userId,
            'authToken' => $authToken,
            'createdDate' => Date::getDateTime ( 'NOW' )->format ( 'Y-m-d H:i:s' ) 
        ) );
    }

    /**
     * Remove an existing auth token
     *
     * @param int $id
     */
    public function removeAuthToken($id) {
        $conn = Application::instance ()->getConnection ();
        $conn->delete ( 'dfl_users_auth_token', array (
            'authTokenId' => $id 
        ) );
    }

    /**
     * Get a list of auth tokens by userId
     *
     * @param int $userId
     * @param int $limit
     * @param int $start
     */
    public function getAuthTokensByUserId($userId, $limit = 5, $start = 0) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
            SELECT * FROM dfl_users_auth_token WHERE userId = :userId
            ORDER BY createdDate DESC
            LIMIT :start,:limit
        ' );
        $stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
        $stmt->bindValue ( 'start', $start, \PDO::PARAM_INT );
        $stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
        $stmt->execute ();
        return $stmt->fetchAll ();
    }

    /**
     * Get a single auth token
     *
     * @param string $authToken
     * @return array<array>
     */
    public function getAuthToken($authToken) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( 'SELECT * FROM dfl_users_auth_token WHERE authToken = :authToken LIMIT 0,1' );
        $stmt->bindValue ( 'authToken', $authToken, \PDO::PARAM_INT );
        $stmt->execute ();
        return $stmt->fetch ();
    }

    /**
     * Check if an auth token exists
     *
     * @param string $authToken
     * @return boolean
     */
    public function getAuthTokenExists($authToken) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( 'SELECT COUNT(*) FROM dfl_users_auth_token WHERE authToken = :authToken LIMIT 0,1' );
        $stmt->bindValue ( 'authToken', $authToken, \PDO::PARAM_INT );
        $stmt->execute ();
        return ($stmt->fetchColumn () == 1) ? true : false;
    }

    /**
     * Get a list of authenticated sessions
     *
     * @param int $userId
     * @param int $limit
     * @param int $start
     */
    public function getAuthSessionsByUserId($userId, $limit = 5, $start = 0) {
        $conn = Application::instance ()->getConnection ();
        $stmt = $conn->prepare ( '
            SELECT * FROM dfl_users_auth WHERE userId = :userId
            ORDER BY createdDate DESC
            LIMIT :start,:limit
        ' );
        $stmt->bindValue ( 'userId', $userId, \PDO::PARAM_INT );
        $stmt->bindValue ( 'start', $start, \PDO::PARAM_INT );
        $stmt->bindValue ( 'limit', $limit, \PDO::PARAM_INT );
        $stmt->execute ();
        return $stmt->fetchAll ();
    }

}