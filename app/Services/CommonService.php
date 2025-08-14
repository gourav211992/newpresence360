<?php

namespace App\Services;
use App\Helpers\FileUploadHelper;
use Illuminate\Http\Request;
use App\Helpers\GeneralHelper;

class CommonService
{
    protected $fileUploadHelper;
    public function __construct(FileUploadHelper $fileUploadHelper)
    {
        $this->fileUploadHelper = $fileUploadHelper;
    }
    public function createBankInfo(array $bankInfoData, $morphable)
    {
        foreach ($bankInfoData as $data) {
            if (!empty($data['bank_name']) && !empty($data['account_number'])) {
                $isPrimary = isset($data['primary']) && $data['primary'] === '1';
                if ($isPrimary) {
                    $morphable->bankInfos()->update(['primary' => '0']);
                }
                $bankInfo = $morphable->bankInfos()->create([
                    'bank_name' => $data['bank_name'] ?? null,
                    'beneficiary_name' => $data['beneficiary_name'] ?? null,
                    'account_number' => $data['account_number'] ?? null,
                    're_enter_account_number' => $data['re_enter_account_number'] ?? null,
                    'ifsc_code' => $data['ifsc_code'] ?? null,
                    'primary' => $isPrimary ? '1' : '0',
                ]);
            
                if (isset($data['cancel_cheque'])) {
                    $this->handleFileUpload($data, $bankInfo);
                }
            }
        }
    }
    
    public function updateBankInfo(array $bankInfoData, $model)
    {
        $existingBankInfoIds = $model->bankInfos()->pluck('id')->toArray();
        $newBankInfos = [];
        foreach ($bankInfoData as $data) {
            if (!empty($data['bank_name']) && !empty($data['account_number'])) {
            if (isset($data['id']) && !empty($data['id'])) {
                $existingBankInfo = $model->bankInfos()->where('id', $data['id'])->first();

                if ($existingBankInfo) {
                    $isPrimary = $data['primary'] ?? $existingBankInfo->primary;
                    if ($isPrimary === '1') {
                        $model->bankInfos()->where('primary', '1')->where('id', '!=', $data['id'])->update(['primary' => '0']);
                    }
                    $existingBankInfo->update([
                        'bank_name' => $data['bank_name'] ?? $existingBankInfo->bank_name,
                        'beneficiary_name' => $data['beneficiary_name'] ?? $existingBankInfo->beneficiary_name,
                        'account_number' => $data['account_number'] ?? $existingBankInfo->account_number,
                        're_enter_account_number' => $data['re_enter_account_number'] ?? $existingBankInfo->re_enter_account_number,
                        'ifsc_code' => $data['ifsc_code'] ?? $existingBankInfo->ifsc_code,
                        'primary' => $isPrimary,
                    ]);
                    $newBankInfos[] = $existingBankInfo->id;
                    $this->handleFileUpload($data, $existingBankInfo);
                }
            } else {
                if (!empty($data['bank_name'])) {
                    $newBankInfo = $model->bankInfos()->create([
                        'bank_name' => $data['bank_name'] ?? null,
                        'beneficiary_name' => $data['beneficiary_name'] ?? null,
                        'account_number' => $data['account_number'] ?? null,
                        're_enter_account_number' => $data['re_enter_account_number'] ?? null,
                        'ifsc_code' => $data['ifsc_code'] ?? null,
                        'primary' => $data['primary'] ?? '0',
                    ]);
                    $newBankInfos[] = $newBankInfo->id;
                    $this->handleFileUpload($data, $newBankInfo);
                }
            }
         }
        }
        $bankInfosToDelete = array_diff($existingBankInfoIds, $newBankInfos);
        if ($bankInfosToDelete) {
            $model->bankInfos()->whereIn('id', $bankInfosToDelete)->delete();
        }

        return true;
    }
    private function handleFileUpload(array $data, $bankInfo)
    {
        if (isset($data['cancel_cheque'])) {
            $fileConfigs = [
                'cancel_cheque' => ['folder' => 'cancel_cheques', 'clear_existing' => true],
            ];
            $request = new Request();
            $request->files->set('cancel_cheque', $data['cancel_cheque']);
            $this->fileUploadHelper->handleFileUploads($request, $bankInfo, $fileConfigs);
        }
    }
    public function createNote($data, $morphable, $user)
    {
        if (!empty($data['id'])) {
            $note = $morphable->notes()->find($data['id']);
            if ($note) {
                $note->remark = $data['remark'];
                $note->updated_by = $user->id; 
                $note->updated_at = now();     
                $note->save();
                return $note;
            }
        }
        $note = $morphable->notes()->create($data);
        $note->created_by = $user->id;
        $note->created_by_type = GeneralHelper::loginUserType();
        $note->save();
    
        return $note;
    }
    

    public function createContact(array $data, $morphable) {
        foreach ($data as $contact) {
            if (!empty($contact['name']) || !empty($contact['mobile'])) {

            if (isset($contact['primary']) && $contact['primary'] == '1') {

            } else {
                $contact['primary'] = '0';
            }
            $morphable->contacts()->create($contact);
         }
        }
    }
    public function updateContact(array $data, $morphable)
    {
        $existingContacts = $morphable->contacts()->pluck('id')->toArray();
        $newContacts = [];

        foreach ($data as $contactData) {
            if (!empty($contactData['name']) || !empty($contactData['mobile'])) {
            if (isset($contactData['id']) && !empty($contactData['id'])) {
                $existingContact = $morphable->contacts()->where('id', $contactData['id'])->first();
                if ($existingContact) {
                    $existingContact->update($contactData);
                    $newContacts[] = $existingContact->id;
                } else {
                    $newContact = $morphable->contacts()->create($contactData);
                    $newContacts[] = $newContact->id;
                }
            } else {
                $newContact = $morphable->contacts()->create($contactData);
                $newContacts[] = $newContact->id;
            }
         } 
        }
        $contactsToDelete = array_diff($existingContacts, $newContacts);
        if ($contactsToDelete) {
            $morphable->contacts()->whereIn('id', $contactsToDelete)->delete();
        }
    }
    
    public function createAddress(array $data, $morphable) {
        $isVendor = $morphable instanceof \App\Models\Vendor; 
        foreach ($data as $address) {
        if (!empty($address['country_id']) && !empty($address['state_id']) && !empty($address['city_id'])) {
            if ($isVendor) {
                $type = 'billing';
                $is_billing = 1;
                $is_shipping = 0;
            } else {
                if (isset($address['is_billing']) && $address['is_billing'] == '1' && isset($address['is_shipping']) && $address['is_shipping'] == '1') {
                    $type = 'both';
                } elseif (isset($address['is_billing']) && $address['is_billing'] == '1') {
                    $type = 'billing';
                } elseif (isset($address['is_shipping']) && $address['is_shipping'] == '1') {
                    $type = 'shipping';
                } else {
                    $type = '';
                }
                $is_billing = isset($address['is_billing']) && $address['is_billing'] == '1' ? 1 : 0;
                $is_shipping = isset($address['is_shipping']) && $address['is_shipping'] == '1' ? 1 : 0;
            }
            $addressData = array_merge([
                'is_billing' => $is_billing,
                'is_shipping' => $is_shipping,
                'type' => $type, 
            ], $address);
            $morphable->addresses()->create($addressData);
        }
      }
    }
    public function updateAddress(array $data, $morphable)
    {
        $isVendor = $morphable instanceof \App\Models\Vendor;
        $existingAddresses = $morphable->addresses()->pluck('id')->toArray();
        $newAddresses = [];
        foreach ($data as $address) {
            if (!empty($address['country_id']) && !empty($address['state_id']) && !empty($address['city_id'])) {
                 if ($isVendor) {
                $type = 'billing';
                $is_billing = 1;
                $is_shipping = 0;
            } else {
                $is_billing = isset($address['is_billing']) && $address['is_billing'] == '1' ? 1 : 0;
                $is_shipping = isset($address['is_shipping']) && $address['is_shipping'] == '1' ? 1 : 0;

                if ($is_billing && $is_shipping) {
                    $type = 'both';
                } elseif ($is_billing) {
                    $type = 'billing';
                } elseif ($is_shipping) {
                    $type = 'shipping';
                } else {
                    $type = '';
                }
            }
            $addressData = array_merge([
                'is_billing' => $is_billing,
                'is_shipping' => $is_shipping,
                'type' => $type,
            ], $address);

            if (isset($address['id']) && !empty($address['id'])) {
                $existingAddress = $morphable->addresses()->where('id', $address['id'])->first();
                if ($existingAddress) {
                    $existingAddress->update($addressData);
                    $newAddresses[] = $existingAddress->id;
                } else {
                    $newAddress = $morphable->addresses()->create($addressData);
                    $newAddresses[] = $newAddress->id;
                }
            } else {
                $newAddress = $morphable->addresses()->create($addressData);
                $newAddresses[] = $newAddress->id;
            }
          } 
        }
        $addressesToDelete = array_diff($existingAddresses, $newAddresses);
        if ($addressesToDelete) {
            $morphable->addresses()->whereIn('id', $addressesToDelete)->delete();
        }
    }

    public function createCompliance(array $data, $morphable)
    {
        $data['msme_registered'] = isset($data['msme_registered']) ? 1 : 0;
        $data['tds_applicable'] = isset($data['tds_applicable']) ? 1 : 0;
        if (isset($data['gst_applicable'])) {
        $compliance = $morphable->compliances()->create($data);

        $fileConfigs = [
            'gst_certificate' => ['folder' => 'gst_certificate', 'clear_existing' => false],
            'msme_certificate' => ['folder' => 'msme_certificate', 'clear_existing' => false],
        ];

        $request = new Request();
        if (isset($data['gst_certificate'])) {
            $request->files->set('gst_certificate', $data['gst_certificate']);
        }
        if (isset($data['msme_certificate'])) {
            $request->files->set('msme_certificate', $data['msme_certificate']);
        }

        $filePaths = $this->fileUploadHelper->handleFileUploads($request, $compliance, $fileConfigs);

        $compliance->update([
            'gst_certificate' => $filePaths['gst_certificate'] ?? $compliance->gst_certificate,
            'msme_certificate' => $filePaths['msme_certificate'] ?? $compliance->msme_certificate,
        ]);

        return $compliance;
        } 
    }

    public function updateCompliance(array $data, $vendor)
    {
        $data['msme_registered'] = isset($data['msme_registered']) ? 1 : 0;
        $data['tds_applicable'] = isset($data['tds_applicable']) ? 1 : 0;
        $compliance = $vendor->compliances()->first();

        if ($compliance) {
            $compliance->update($data);
        } else {
            $compliance = $vendor->compliances()->create($data);
        }

        $fileConfigs = [
            'gst_certificate' => ['folder' => 'gst_certificate', 'clear_existing' => true],
            'msme_certificate' => ['folder' => 'msme_certificate', 'clear_existing' => true],
        ];

        $request = new Request();
        if (isset($data['gst_certificate'])) {
            $request->files->set('gst_certificate', $data['gst_certificate']);
        }
        if (isset($data['msme_certificate'])) {
            $request->files->set('msme_certificate', $data['msme_certificate']);
        }

        $filePaths = $this->fileUploadHelper->handleFileUploads($request, $compliance, $fileConfigs);
        $compliance->update([
            'gst_certificate' => $filePaths['gst_certificate'] ?? $compliance->gst_certificate,
            'msme_certificate' => $filePaths['msme_certificate'] ?? $compliance->msme_certificate,
        ]);

        return $compliance;
    }

}

