$baseUrl = 'http://localhost/Billing-system/public'

$loginPage = Invoke-WebRequest -Uri "$baseUrl/login" -SessionVariable session -UseBasicParsing
$csrf = [regex]::Match($loginPage.Content, 'name="_token" value="([^"]+)"').Groups[1].Value
Write-Host "Login CSRF: $(-not [string]::IsNullOrEmpty($csrf))"

$loginResult = Invoke-WebRequest -Uri "$baseUrl/login" -Method POST -WebSession $session -Body @{
    '_token' = $csrf
    'email' = 'admin@example.com'
    'password' = 'Admin123!'
} -UseBasicParsing
Write-Host "Login: OK"

$indexPage = Invoke-WebRequest -Uri "$baseUrl/admin/carousels" -WebSession $session -UseBasicParsing
Write-Host "Index: $($indexPage.StatusCode)"

$seededCount = [regex]::Matches($indexPage.Content, 'Promo Special Weekend|Game Terbaru|Member Baru').Count
Write-Host "Seeded carousels: $seededCount"
