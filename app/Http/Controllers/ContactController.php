<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Contacts")
 */
class ContactController extends Controller
{
    /**
     * @OA\Get(
     *     path="/contacts",
     *     tags={"Contacts"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get list of contacts",
     *     @OA\Response(
     *         response=200,
     *         description="List contacts",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Contact"))
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $contacts = Contact::where('user_id', auth()->user()->id)->get();
        return response()->json(ContactResource::collection($contacts));
    }

    /**
     * @OA\Post(
     *     path="/contacts",
     *     tags={"Contacts"},
     *     security={{"bearerAuth":{}}},
     *     summary="Create a new contact",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ContactCreate")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Contact created",
     *         @OA\JsonContent(ref="#/components/schemas/Contact")
     *     )
     * )
     */
    public function store(ContactRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->user()->id;
        $contact = Contact::create($data);

        return (new ContactResource($contact))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/contacts/{id}",
     *     tags={"Contacts"},
     *     security={{"bearerAuth":{}}},
     *     summary="Get a contact by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Contact ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact retrieved",
     *         @OA\JsonContent(ref="#/components/schemas/Contact")
     *     ),
     *     @OA\Response(response=404, description="Contact not found")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $contact = Contact::where('user_id', auth()->user()->id)->find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact not found'], 404);
        }

        return response()->json(new ContactResource($contact));
    }

    /**
     * @OA\Put(
     *     path="/contacts/{id}",
     *     tags={"Contacts"},
     *     security={{"bearerAuth":{}}},
     *     summary="Update a contact",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Contact ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ContactCreate")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact updated",
     *         @OA\JsonContent(ref="#/components/schemas/Contact")
     *     ),
     *     @OA\Response(response=404, description="Contact not found")
     * )
     */
    public function update(ContactRequest $request, int $id): JsonResponse
    {
        $contact = Contact::where('user_id', auth()->user()->id)->find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact not found'], 404);
        }

        $contact->update($request->validated());

        return response()->json(new ContactResource($contact));
    }

    /**
     * @OA\Delete(
     *     path="/contacts/{id}",
     *     tags={"Contacts"},
     *     security={{"bearerAuth":{}}},
     *     summary="Delete a contact",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Contact ID"
     *     ),
     *     @OA\Response(response=204, description="Contact deleted"),
     *     @OA\Response(response=404, description="Contact not found")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $contact = Contact::where('user_id', auth()->user()->id)->find($id);

        if (!$contact) {
            return response()->json(['message' => 'Contact not found'], 404);
        }

        $contact->delete();
        return response()->json(null, 204);
    }
}
