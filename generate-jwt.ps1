$header = @{
    alg = "HS256"
    typ = "JWT"
} | ConvertTo-Json -Compress

$payload = @{
    iss = "supabase"
    ref = "localhost"
    role = "anon"
    iat = [int][double]::Parse((Get-Date -UFormat %s))
    exp = [int][double]::Parse((Get-Date -UFormat %s)) + (10 * 365 * 24 * 60 * 60)  # 10 ans
} | ConvertTo-Json -Compress

$encodedHeader = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($header)).Replace('+', '-').Replace('/', '_').TrimEnd('=')
$encodedPayload = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($payload)).Replace('+', '-').Replace('/', '_').TrimEnd('=')

$jwt = "$encodedHeader.$encodedPayload"

$secret = "your-super-secret-key-minimum-32-characters"
$hmacsha = New-Object System.Security.Cryptography.HMACSHA256
$hmacsha.key = [System.Text.Encoding]::UTF8.GetBytes($secret)
$signature = $hmacsha.ComputeHash([System.Text.Encoding]::UTF8.GetBytes($jwt))
$encodedSignature = [Convert]::ToBase64String($signature).Replace('+', '-').Replace('/', '_').TrimEnd('=')

$token = "$jwt.$encodedSignature"

Write-Output "Votre SUPABASE_KEY JWT :"
Write-Output $token
