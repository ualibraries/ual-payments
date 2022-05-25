FROM  tier/shibboleth_sp:3.3.0_12022021

RUN /etc/shibboleth/keygen.sh -o /etc/shibboleth/ -y 10 -n sp-encrypt -f \
     && /etc/shibboleth/keygen.sh -o /etc/shibboleth/ -y 10 -n sp-signing -f

#      ...those commands generate/overwrite the following files:
#                  /etc/shibboleth/sp-encrypt-key.pem
#                  /etc/shibboleth/sp-encrypt-cert.pem
#                  /etc/shibboleth/sp-signing-key.pem
#                  /etc/shibboleth/sp-signing-cert.pem