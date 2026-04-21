<?php

/*
 * Security settings page is not implemented in this application.
 * Fortify routes are ignored (Fortify::ignoreRoutes()) and no settings routes exist.
 */

test('security settings is not applicable', function () {
    $this->markTestSkipped('Security settings page is not implemented in this application.');
});
