$baseUrl = 'http://localhost/Billing-system/public'

# 1. Login
$loginPage = Invoke-WebRequest -Uri "$baseUrl/login" -SessionVariable session -UseBasicParsing
$csrf = [regex]::Match($loginPage.Content, 'name="_token" value="([^"]+)"').Groups[1].Value
Invoke-WebRequest -Uri "$baseUrl/login" -Method POST -WebSession $session -Body @{
    '_token' = $csrf
    'email' = 'admin@example.com'
    'password' = 'Admin123!'
} -UseBasicParsing | Out-Null
Write-Host "Logged in"

# 2. Get Create Form
$createPage = Invoke-WebRequest -Uri "$baseUrl/admin/carousels/create" -WebSession $session -UseBasicParsing
$csrf = [regex]::Match($createPage.Content, 'name="_token" value="([^"]+)"').Groups[1].Value
Write-Host "Create form retrieved"

# 3. Store Carousel
try {
    $storeResult = Invoke-WebRequest -Uri "$baseUrl/admin/carousels" -Method POST -WebSession $session -Body @{
        '_token' = $csrf
        'title' = 'Test Tahap 12'
        'description' = 'Testing'
        'sort_order' = '50'
        'is_active' = 'on'
    } -UseBasicParsing -ErrorAction Stop
    
    Write-Host "Store Status: $($storeResult.StatusCode)"
    Write-Host "Redirected to: $($storeResult.BaseResponse.RequestMessage.RequestUri)"
    
    if ($storeResult.Content -match "Test Tahap 12") {
        Write-Host "Title found in response"
    } else {
        Write-Host "Title NOT in response"
    }
} catch {
    Write-Host "Error: $($_.Exception.Message)"
}

# 4. Check database count
Write-Host "`nDatabase check..."
