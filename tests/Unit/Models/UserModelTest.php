<?php

namespace Tests\Unit\Models;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * White-Box Test: UserModel Fillable Attributes & Hidden Serialization
     */
    public function test_user_fillable_and_hidden_attributes()
    {
        $user = new User([
            'name' => 'John Engineer',
            'email' => 'john@example.com',
            'role' => 'admin',
            'supplier_id' => 5,
            'password' => 'secret123',
        ]);

        $this->assertEquals('John Engineer', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals('admin', $user->role);
        $this->assertEquals(5, $user->supplier_id);

        $array = $user->toArray();
        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    /**
     * White-Box Test: isAdmin() Method Branch Coverage
     * Path 1: empty role (null) -> true
     * Path 2: empty role ('') -> true
     * Path 3: role is 'admin' -> true
     * Path 4: role is 'roofing_transfer' -> false
     * Path 5: role is 'supplier' -> false
     */
    public function test_is_admin_method_branches()
    {
        $userNullRole = new User(['role' => null]);
        $this->assertTrue($userNullRole->isAdmin());

        $userEmptyRole = new User(['role' => '']);
        $this->assertTrue($userEmptyRole->isAdmin());

        $userAdmin = new User(['role' => 'admin']);
        $this->assertTrue($userAdmin->isAdmin());

        $userRoofing = new User(['role' => 'roofing_transfer']);
        $this->assertFalse($userRoofing->isAdmin());

        $userSupplier = new User(['role' => 'supplier']);
        $this->assertFalse($userSupplier->isAdmin());
    }

    /**
     * White-Box Test: isRoofingOfficer() Method Branch Coverage
     * Path 1: role is 'roofing_transfer' -> true
     * Path 2: other roles -> false
     */
    public function test_is_roofing_officer_method_branches()
    {
        $userRoofing = new User(['role' => 'roofing_transfer']);
        $this->assertTrue($userRoofing->isRoofingOfficer());

        $userAdmin = new User(['role' => 'admin']);
        $this->assertFalse($userAdmin->isRoofingOfficer());

        $userWindows = new User(['role' => 'windows_doors_transfer']);
        $this->assertFalse($userWindows->isRoofingOfficer());
    }

    /**
     * White-Box Test: isWindowsDoorsOfficer() Method Branch Coverage
     * Path 1: role is 'windows_doors_transfer' -> true
     * Path 2: other roles -> false
     */
    public function test_is_windows_doors_officer_method_branches()
    {
        $userWindows = new User(['role' => 'windows_doors_transfer']);
        $this->assertTrue($userWindows->isWindowsDoorsOfficer());

        $userAdmin = new User(['role' => 'admin']);
        $this->assertFalse($userAdmin->isWindowsDoorsOfficer());

        $userRoofing = new User(['role' => 'roofing_transfer']);
        $this->assertFalse($userRoofing->isWindowsDoorsOfficer());
    }

    /**
     * White-Box Test: isSupplier() Method Branch Coverage
     * Path 1: role === 'supplier' (supplier_id null) -> true
     * Path 2: role is null/empty but supplier_id is set -> true
     * Path 3: role === 'admin' and supplier_id is null -> false
     */
    public function test_is_supplier_method_branches()
    {
        $supplierRole = new User(['role' => 'supplier', 'supplier_id' => null]);
        $this->assertTrue($supplierRole->isSupplier());

        $supplierIdSet = new User(['role' => null, 'supplier_id' => 12]);
        $this->assertTrue($supplierIdSet->isSupplier());

        $adminUser = new User(['role' => 'admin', 'supplier_id' => null]);
        $this->assertFalse($adminUser->isSupplier());
    }

    /**
     * White-Box Test: getRoleTitleAttribute Branch Coverage
     * Path 1: isSupplier() is true with associated supplier model
     * Path 2: isSupplier() is true without loaded supplier relation
     * Path 3: role is 'roofing_transfer'
     * Path 4: role is 'windows_doors_transfer'
     * Path 5: default / admin
     */
    public function test_role_title_attribute_branches()
    {
        $supplier = Supplier::create([
            'name' => 'Apex Roofing Supply',
            'code' => 'SUP-APEX',
            'email' => 'sales@apexroofing.test',
            'category' => 'Roofing',
            'status' => 'active',
        ]);

        $supplierUserWithRelation = User::create([
            'name' => 'Apex Agent',
            'email' => 'apex@example.com',
            'role' => 'supplier',
            'supplier_id' => $supplier->id,
            'password' => bcrypt('secret'),
        ]);
        $supplierUserWithRelation->load('supplier');
        $this->assertEquals('Apex Roofing Supply (Roofing)', $supplierUserWithRelation->role_title);

        $supplierUserWithoutRelation = new User([
            'role' => 'supplier',
            'supplier_id' => null,
        ]);
        $this->assertEquals('Supplier Account', $supplierUserWithoutRelation->role_title);

        $roofingUser = new User(['role' => 'roofing_transfer']);
        $this->assertEquals('Roofing Transfer Officer', $roofingUser->role_title);

        $windowsUser = new User(['role' => 'windows_doors_transfer']);
        $this->assertEquals('Windows & Doors Transfer Officer', $windowsUser->role_title);

        $adminUser = new User(['role' => 'admin']);
        $this->assertEquals('Master Administrator', $adminUser->role_title);

        $nullUser = new User(['role' => null]);
        $this->assertEquals('Master Administrator', $nullUser->role_title);
    }

    /**
     * White-Box Test: getPortalRouteAttribute Branch Coverage
     * Path 1: isSupplier() is true -> route('supplier.dashboard')
     * Path 2: role is 'roofing_transfer' -> route('roofing.index')
     * Path 3: role is 'windows_doors_transfer' -> route('windowsDoors.index')
     * Path 4: default / admin -> route('dashboard')
     */
    public function test_portal_route_attribute_branches()
    {
        $supplierUser = new User(['role' => 'supplier']);
        $this->assertEquals(route('supplier.dashboard'), $supplierUser->portal_route);

        $roofingUser = new User(['role' => 'roofing_transfer']);
        $this->assertEquals(route('roofing.index'), $roofingUser->portal_route);

        $windowsUser = new User(['role' => 'windows_doors_transfer']);
        $this->assertEquals(route('windowsDoors.index'), $windowsUser->portal_route);

        $adminUser = new User(['role' => 'admin']);
        $this->assertEquals(route('dashboard'), $adminUser->portal_route);
    }

    /**
     * White-Box Test: supplier() BelongsTo Relationship
     */
    public function test_user_supplier_relationship()
    {
        $supplier = Supplier::create([
            'name' => 'Solid Glass Co.',
            'code' => 'SUP-GLASS',
            'email' => 'sales@solidglass.test',
            'category' => 'Windows & Doors',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Glass Manager',
            'email' => 'glass@example.com',
            'role' => 'supplier',
            'supplier_id' => $supplier->id,
            'password' => bcrypt('password123'),
        ]);

        $this->assertInstanceOf(Supplier::class, $user->supplier);
        $this->assertEquals($supplier->id, $user->supplier->id);
    }
}
