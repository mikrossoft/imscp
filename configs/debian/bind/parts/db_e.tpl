$TTL 12H
$ORIGIN {DMN_NAME}.
@               IN              SOA             ns1.{DMN_NAME}. postmaster.{DMN_NAME}. (
; dmn [{DMN_NAME}] timestamp entry BEGIN.
                {TIMESTAMP}     ; Serial
; dmn [{DMN_NAME}] timestamp entry END.
                8H              ; Refresh
                2H              ; Retry
                7D              ; Expire
                1D              ; Minimum TTL
)
; ns DECLARATION SECTION BEGIN
                IN              NS              ns{NS_NUMBER}.{DMN_NAME}.
; ns DECLARATION SECTION END

                IN              MX      10      mail.{DMN_NAME}.

{DMN_NAME}.     IN              A               {DMN_IP}
www             IN              A               {DMN_IP}
{DMN_NAME}.     IN              TXT             "v=spf1 a mx ip4:{DMN_IP} ip4:{BASE_SERVER_IP} ~all"
localhost       IN              A               127.0.0.1
mail            IN              A               {DMN_IP}
; ns A SECTION BEGIN
ns{NS_NUMBER}             IN              A               {NS_IP}
; ns A SECTION END

; CNAME for mail transfer
imap            IN              CNAME           mail
pop             IN              CNAME           mail
pop3            IN              CNAME           mail
relay           IN              CNAME           mail
smtp            IN              CNAME           mail
; CNAME for web transfer
ftp             IN              CNAME           www

; sub [{SUB_NAME}] entry BEGIN.
; sub [{SUB_NAME}] entry END.

; dns manual entry BEGIN.
; dns manual entry END.

; ctm domain als entries BEGIN.
; ctm domain als entries END.
;
