# geekcell/sodium-bundle

A Symfony bundle to interact with [PHP's Sodium](https://www.php.net/manual/de/book.sodium.php) extension.

## Installation

To use this package, require it in your Symfony project with Composer.

```bash
composer require geekcell/sodium-bundle
```

Verify that the bundle has been enabled in `config/bundles.php`

```php
<?php

return [
    // other bundles ...
    GeekCell\SodiumBundle\GeekCellSodiumBundle::class => ['all' => true],
];
```

## Limitations

At this point in time, this bundle only supports [libsodium's](https://doc.libsodium.org/) [anonymous](https://doc.libsodium.org/public-key_cryptography/sealed_boxes) and [authenticated](https://doc.libsodium.org/public-key_cryptography/authenticated_encryption) public-key encryption.

## Configuration

Create a config file `config/packages/geek_cell_sodium.yaml` where you configure your base64-encoded public and private/secret keys for encryption and decryption. It is very strongly recommended to not store them as plain text, but read them from your `.env.local`, which is added to your `.gitignore` file.

```yaml
geek_cell_sodium:
    public_key: '%env(SODIUM_PUBLIC_KEY)%'
    private_key: '%env(SODIUM_PRIVATE_KEY)%'
```

Only the `public_key` field is mandatory, if you only plan for anonymous (shared) public-key encryption in your app. For both authenticated and anonymous decryption, a `private_key` must also be configured, or an exception is thrown during runtime.

This bundle ships with a console command `sodium:generate-keys` to generate a set of public/private keys for you.

```
❯ bin/console sodium:generate-keys
Generating a new set of public and private keys...

Public Key:  cqJZXt1dhZtyYZ0NcOmwkgcyvW2t9w2Wdwe/Wk6zegk=
Private Key: G3XKnSunNpN1LHKY34LFen7XI2dmu6xBk9UeTQIxNwY=

Please add or update the following environment variables in your .env.local file:

SODIUM_PUBLIC_KEY=cqJZXt1dhZtyYZ0NcOmwkgcyvW2t9w2Wdwe/Wk6zegk=
SODIUM_PRIVATE_KEY=G3XKnSunNpN1LHKY34LFen7XI2dmu6xBk9UeTQIxNwY=

Done!
```

## Usage

Simply typehint the `GeekCell\SodiumBundle\Sodium\Sodium` service in your code and make use of its `encrypt` and `decrypt` methods:

### Anonymous encryption

The example below demonstrates _anonymous_ encryption using only a shared public key. In order to decrypt a message, the receiver needs both public and corresponding private/secret key.

```php
<?php

// Sender

namespace Alice\Service;

use GeekCell\SodiumBundle\Sodium\Sodium;

class AnonymousEncryptionService
{
    public function __construct(
        private readonly Sodium $sodium,
    ) {}

    public function encryptMessage(string $message): string
    {
        return $this->sodium
            ->with('box')
            ->encrypt($message)
        ;
    }
}
```

```php
<?php

// Receiver

namespace Bob\Service;

use GeekCell\SodiumBundle\Sodium\Sodium;

class AnonymousDecryptionService
{
    public function __construct(
        private readonly Sodium $sodium,
    ) {}

    public function decryptMessage(string $message): string
    {
        return $this->sodium
            ->with('box')
            ->decrypt($message)
        ;
    }
}
```

### Authenticated encryption

Alternatively you can use _authenticated_ public-key encyption to encrypt specifically encrypt messages by using a recipient's public key and a nonce. When received, the recipient can then decrypt a cipher using the sender's public key and nonce.

```php
// Sender

namespace Alice\Service;

use GeekCell\SodiumBundle\Sodium\Sodium;

class AuthenicatedEncryptionService
{
    public function __construct(
        private readonly Sodium $sodium,
    ) {}

    public function encryptMessage(string $message, string $recipientPublicKey, $string $nonce): string
    {
        return $this->sodium
            ->with('box')
            ->for($recipientPublicKey)
            ->encrypt($message, $nonce)
        ;
    }
}
```

```php
<?php

// Receiver

namespace Bob\Service;

use GeekCell\SodiumBundle\Sodium\Sodium;

class AuthenticatedDecryptionService
{
    public function __construct(
        private readonly Sodium $sodium,
    ) {}

    public function decryptMessage(string $message, string $senderPublicKey, string $nonce): string
    {
        return $this->sodium
            ->with('box')
            ->from($senderPublicKey)
            ->decrypt($message, $nonce)
        ;
    }
}
```

### Facade

For situations where you cannot inject `GeekCell\SodiumBundle\Sodium\Sodium` via Symfony's DIC (for example if you want to directly encrypt or decrypt fields of your Doctine entity), you can use a [container-facade](https://github.com/geekcell/container-facade) for your convenience:

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use GeekCell\SodiumBundle\Support\Facade\Sodium;

#[ORM\Entity]
class DiaryEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $date;

    #[Column(type: 'text')]
    private string $encryptedEntry;

    public function setEntry(string $entry): void
    {
        $this->encryptedEntry = Sodium::with('box')->encrypt($entry);
    }

    // ...
}
```

For more information, check out [geekcell/container-facade](https://github.com/geekcell/container-facade).
