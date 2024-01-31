<?php
/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */
include_once 'Autoloader/Autoloader.php';
include_once 'Regions/EndpointConfig.php';

//config sdk auto load path.
AlipayAutoloader::addAutoloadPath("aliyun-php-sdk-ecs");
AlipayAutoloader::addAutoloadPath("aliyun-php-sdk-batchcompute");
AlipayAutoloader::addAutoloadPath("aliyun-php-sdk-sts");
AlipayAutoloader::addAutoloadPath("aliyun-php-sdk-push");
AlipayAutoloader::addAutoloadPath("aliyun-php-sdk-ram");
AlipayAutoloader::addAutoloadPath("aliyun-php-sdk-ubsms");
AlipayAutoloader::addAutoloadPath("aliyun-php-sdk-ubsms-inner");
AlipayAutoloader::addAutoloadPath("aliyun-php-sdk-green");
AlipayAutoloader::addAutoloadPath("aliyun-php-sdk-dm");
AlipayAutoloader::addAutoloadPath("aliyun-php-sdk-iot");
AlipayAutoloader::addAutoloadPath("aliyun-php-sdk-jaq");
AlipayAutoloader::addAutoloadPath("aliyun-php-sdk-cs");
AlipayAutoloader::addAutoloadPath("aliyun-php-sdk-live");
AlipayAutoloader::addAutoloadPath("aliyun-php-sdk-vpc");
AlipayAutoloader::addAutoloadPath("aliyun-php-sdk-kms");
AlipayAutoloader::addAutoloadPath("aliyun-php-sdk-rds");
AlipayAutoloader::addAutoloadPath("aliyun-php-sdk-slb");
AlipayAutoloader::addAutoloadPath("aliyun-php-sdk-cms");

//config http proxy	
define('ENABLE_HTTP_PROXY', FALSE);
define('HTTP_PROXY_IP', '127.0.0.1');
define('HTTP_PROXY_PORT', '8888');
