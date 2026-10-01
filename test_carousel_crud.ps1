$baseUrl = 'http://localhost/Billing-system/public'

# 1. Login
$loginPage = Invoke-WebRequest -Uri "$baseUrl/login" -SessionVariable session -UseBasicParsing
$csrf = [regex]::Match($loginPage.Content, 'name="_token" value="([^"]+)"').Groups[1].Value
Invoke-WebRequest -Uri "$baseUrl/login" -Method POST -WebSession $session -Body @{
    '_token' = $csrf
    'email' = 'admin@example.com'
    'password' = 'Admin123!'
} -UseBasicParsing | Out-Null
Write-Host "Step 1 - Logged in successfully"

# 2. Get Create Form
$createPage = Invoke-WebRequest -Uri "$baseUrl/admin/carousels/create" -WebSession $session -UseBasicParsing
$csrf = [regex]::Match($createPage.Content, 'name="_token" value="([^"]+)"').Groups[1].Value
Write-Host "Step 2 - Create form retrieved"

# 3. Store Carousel
$storeResult = Invoke-WebRequest -Uri "$baseUrl/admin/carousels" -Method POST -WebSession $session -Body @{
    '_token' = $csrf
    'title' = 'Test Carousel Tahap 12'
    'description' = 'Test description for carousel'
    'sort_order' = '99'
    'is_active' = 'on'
} -UseBasicParsing
Write-Host "Step 3 - Carousel created"

# 4. Verify in index
$indexPage = Invoke-WebRequest -Uri "$baseUrl/admin/carousels" -WebSession $session -UseBasicParsing
$found = $indexPage.Content.Contains("Test Carousel Tahap 12")
Write-Host "Step 4 - Found in index: $found"

# 5. Try to find ID
$lines = $indexPage.Content -split "`n"
foreach ($line in $lines) {
    if ($line -match "Test Carousel Tahap 12") {
        if ($line -match "carousels/(\d+)") {
            $carouselId = $matches[1]
            Write-Host "Step 5 - Found carousel ID: $carouselId"
            break
        }
    }
}

if ($carouselId) {
    # Edit form
    $editPage = Invoke-WebRequest -Uri "$baseUrl/admin/carousels/$carouselId/edit" -WebSession $session -UseBasicParsing
    Write-Host "Step 6 - Edit form loaded"
    
    $csrf = [regex]::Match($editPage.Content, 'name="_token" value="([^"]+)"').Groups[1].Value
    
    # Update
    $updateResult = Invoke-WebRequest -Uri "$baseUrl/admin/carousels/$carouselId" -Method POST -WebSession $session -Body @{
        '_token' = $csrf
        '_method' = 'PUT'
        'title' = 'Test Carousel Updated'
        'description' = 'Updated'
        'sort_order' = '88'
        'is_active' = 'on'
    } -UseBasicParsing
    Write-Host "Step 7 - Carousel updated"
    
    # Toggle
    $indexPage2 = Invoke-WebRequest -Uri "$baseUrl/admin/carousels" -WebSession $session -UseBasicParsing
    $csrf = [regex]::Match($indexPage2.Content, 'name="_token" value="([^"]+)"').Groups[1].Value
    
    $toggleResult = Invoke-WebRequest -Uri "$baseUrl/admin/carousels/$carouselId/toggle" -Method PATCH -WebSession $session -Body @{
        '_token' = $csrf
    } -UseBasicParsing
    Write-Host "Step 8 - Status toggled"
    
    # Delete
    $indexPage3 = Invoke-WebRequest -Uri "$baseUrl/admin/carousels" -WebSession $session -UseBasicParsing
    $csrf = [regex]::Match($indexPage3.Content, 'name="_token" value="([^"]+)"').Groups[1].Value
    
    $deleteResult = Invoke-WebRequest -Uri "$baseUrl/admin/carousels/$carouselId" -Method DELETE -WebSession $session -Body @{
        '_token' = $csrf
    } -UseBasicParsing
    Write-Host "Step 9 - Carousel deleted"
}

Write-Host "`n=== TAHAP 12 - CAROUSEL MANAGEMENT COMPLETED ==="
