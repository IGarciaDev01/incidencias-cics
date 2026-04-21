<?php

/*
 * Registration is admin-only in this application (via panel/admin/usuarios).
 * Public registration is not available, so these tests are not applicable.
 */

test('public registration is not available', function () {
    $this->markTestSkipped('Registration is admin-only in this application.');
});
