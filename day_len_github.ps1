[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
Clear-Host

Write-Host "========================================================" -ForegroundColor Cyan
Write-Host "   🚀 TOOL ĐẨY TOÀN BỘ FILE LÊN GITHUB (THANGCYRUS23)    " -ForegroundColor Yellow
Write-Host "========================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Repo đích: https://github.com/thangcyrus2008-creator/THANGCYRUS23.git" -ForegroundColor Gray
Write-Host ""
Write-Host "[1] Đẩy tự động (Trình duyệt sẽ hiện cửa sổ đăng nhập GitHub)" -ForegroundColor Green
Write-Host "[2] Đẩy bằng Personal Access Token (Dán token vào là đẩy ngay)" -ForegroundColor Magenta
Write-Host ""

$choice = Read-Host "Chọn cách [1 hoặc 2] (Bấm Enter để chọn 1)"

$git = "C:\Users\bestn\AppData\Local\Microsoft\WinGet\Packages\Git.MinGit_Microsoft.Winget.Source_8wekyb3d8bbwe\cmd\git.exe"
$repoPath = $PSScriptRoot

if ($choice -eq "2") {
    Write-Host ""
    $token = Read-Host "Dán GitHub Personal Access Token (ghp_...) vào đây"
    if ([string]::IsNullOrWhiteSpace($token)) {
        Write-Host "[!] Bro chưa nhập Token!" -ForegroundColor Red
        return
    }
    Write-Host ""
    Write-Host "[*] Đang đẩy toàn bộ 1.131 file lên GitHub bằng Token..." -ForegroundColor Yellow
    & $git -C $repoPath push -u "https://$($token)@github.com/thangcyrus2008-creator/THANGCYRUS23.git" main
} else {
    Write-Host ""
    Write-Host "[*] Đang đẩy lên GitHub... Nếu trình duyệt hiện cửa sổ, bro chỉ cần bấm Authorize nhé!" -ForegroundColor Yellow
    & $git -C $repoPath push -u origin main
}

Write-Host ""
if ($LASTEXITCODE -eq 0) {
    Write-Host "========================================================" -ForegroundColor Green
    Write-Host "   🎉 THÀNH CÔNG RỒI BRODY! TOÀN BỘ FILE ĐÃ LÊN GITHUB! " -ForegroundColor Green
    Write-Host "   👉 Xem tại: https://github.com/thangcyrus2008-creator/THANGCYRUS23" -ForegroundColor Cyan
    Write-Host "========================================================" -ForegroundColor Green
} else {
    Write-Host "========================================================" -ForegroundColor Red
    Write-Host "   ⚠️ Đẩy chưa thành công. Bro thử dùng Cách 2 (nhập Token) nhé!" -ForegroundColor Yellow
    Write-Host "   Tạo Token nhanh tại: https://github.com/settings/tokens (chọn ô 'repo')" -ForegroundColor Gray
    Write-Host "========================================================" -ForegroundColor Red
}
