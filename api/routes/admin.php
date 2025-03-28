<?php
// Admin routes
$router->get('/admin/verifications/pending', 'AdminController@getPendingVerifications');
$router->post('/admin/verifications/approve', 'AdminController@approveVerification');
$router->post('/admin/verifications/reject', 'AdminController@rejectVerification');
$router->get('/admin/teachers', 'AdminController@getAllTeachers');

