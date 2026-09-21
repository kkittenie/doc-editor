\ = Get-Content 'c:\\Users\\ACER\\OneDrive\\Documents\\PKL\\doc-editor\\resources\\views\\pages\\documents.blade.php' -Raw
\ = \ -replace 'Kirim Review', 'Kirim Review Test'
Set-Content 'c:\\Users\\ACER\\OneDrive\\Documents\\PKL\\doc-editor\\resources\\views\\pages\\documents.blade.php' -Value \ -NoNewline
