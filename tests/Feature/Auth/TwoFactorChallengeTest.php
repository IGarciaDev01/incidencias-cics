<?php

/*
 * Two-factor challenge routes are not registered (Fortify::ignoreRoutes()).
 * 2FA management is handled via the security settings page if implemented.
 */

test('two factor challenge is not applicable', function () {
    $this->markTestSkipped('Two-factor challenge routes are not registered in this application.');
});
