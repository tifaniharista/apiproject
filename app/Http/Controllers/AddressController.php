<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="Addresses")
 */

/**
 * @OA\Schema(
 *     schema="Address",
 *     type="object",
 *     required={"country"},
 *     @OA\Property(property="street", type="string", example="123 Main St"),
 *     @OA\Property(property="city", type="string", example="Jakarta"),
 *     @OA\Property(property="province", type="string", example="DKI Jakarta"),
 *     @OA\Property(property="country", type="string", example="Indonesia"),
 *     @OA\Property(property="postal_code", type="string", example="12345")
 * )
 */
class AddressController extends Controller
{
    /**
     * @OA\Post(
     *     path="/contacts/{contactId}/addresses",
     *     tags={"Addresses"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create a new address for a contact",
     *     @OA\Parameter(
     *         name="contactId",
     *         in="path",
     *         required=true,
     *         description="ID of the contact",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Address")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Address created",
     *         @OA\JsonContent(ref="#/components/schemas/Address")
     *     )
     * )
     */
    public function store(AddressRequest $request, int $contactId): JsonResponse
    {
        $contact = Contact::where('user_id', auth()->user()->id)->findOrFail($contactId);

        $address = $contact->addresses()->create($request->validated());

        return (new AddressResource($address))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Put(
     *     path="/contacts/{contactId}/addresses/{id}",
     *     tags={"Addresses"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update an address for a contact",
     *     @OA\Parameter(
     *         name="contactId",
     *         in="path",
     *         required=true,
     *         description="ID of the contact",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the address",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Address")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Address updated",
     *         @OA\JsonContent(ref="#/components/schemas/Address")
     *     ),
     *     @OA\Response(response=404, description="Contact or Address not found")
     * )
     */
    public function update(AddressRequest $request, int $contactId, int $id): JsonResponse
    {
        $contact = Contact::where('user_id', auth()->user()->id)->findOrFail($contactId);
        $address = $contact->addresses()->findOrFail($id);

        $address->update($request->validated());

        return response()->json(new AddressResource($address));
    }

    /**
     * @OA\Delete(
     *     path="/contacts/{contactId}/addresses/{id}",
     *     tags={"Addresses"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete an address for a contact",
     *     @OA\Parameter(
     *         name="contactId",
     *         in="path",
     *         required=true,
     *         description="ID of the contact",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the address",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Address deleted"),
     *     @OA\Response(response=404, description="Contact or Address not found")
     * )
     */
    public function destroy(int $contactId, int $id): JsonResponse
    {
        $contact = Contact::where('user_id', auth()->user()->id)->findOrFail($contactId);
        $address = $contact->addresses()->findOrFail($id);

        $address->delete();

        return response()->json(null, 204);
    }
}
